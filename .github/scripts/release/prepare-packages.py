#!/usr/bin/env python3
"""Decide which Hestia backend packages need building for one suite/arch.

For each package, compute the exact version the build would produce. If that
version, or the same upstream version at a higher package revision, is already
published in the channel's apt repo, download the existing deb into OUT_DIR and
skip the build; otherwise emit the matching hst_autocompile.sh flag.

Build flags are printed on stdout; logs go to stderr.
"""

import hashlib
import os
import re
import shutil
import subprocess
import sys
import urllib.request
from pathlib import Path
from typing import Optional

# Keep in sync with .github/scripts/release/publish.sh.
SUITE_SUFFIX = {
    "bookworm": "debian12",
    "trixie": "debian13",
    "jammy": "ubuntu22.04",
    "noble": "ubuntu24.04",
    "resolute": "ubuntu26.04",
}

PACKAGES = [
    ("hestia-nginx", "src/deb/nginx/control", "--nginx"),
    ("hestia-php", "src/deb/php/control", "--php"),
    ("hestia-web-terminal", "src/deb/web-terminal/control", "--web-terminal"),
]


def log(message: str) -> None:
    print(message, file=sys.stderr)


def fetch(url: str) -> bytes:
    request = urllib.request.Request(url, headers={"User-Agent": "curl/8"})
    with urllib.request.urlopen(request, timeout=120) as response:
        return response.read()


def download(url: str, destination: Path) -> None:
    request = urllib.request.Request(url, headers={"User-Agent": "curl/8"})
    with urllib.request.urlopen(request, timeout=300) as response:
        with destination.open("wb") as handle:
            shutil.copyfileobj(response, handle)


def read_control_version(path: str) -> str:
    for line in Path(path).read_text(encoding="utf-8").splitlines():
        if line.startswith("Version:"):
            return line.split(":", 1)[1].strip()
    raise RuntimeError(f"no Version in {path}")


def sha256_file(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def parse_packages(text: str) -> dict[tuple[str, str], tuple[str, str]]:
    result: dict[tuple[str, str], tuple[str, str]] = {}
    for stanza in text.split("\n\n"):
        fields: dict[str, str] = {}
        for line in stanza.splitlines():
            if ": " in line:
                key, value = line.split(": ", 1)
                fields[key] = value
        package = fields.get("Package")
        version = fields.get("Version")
        filename = fields.get("Filename")
        sha256 = fields.get("SHA256")
        if package and version and filename and sha256:
            result[(package, version)] = (filename, sha256.lower())
    return result


def find_published(
    index: dict[tuple[str, str], tuple[str, str]],
    package: str,
    upstream: str,
    suffix: str,
    tag: str,
) -> Optional[tuple[int, str]]:
    """Return (revision, version) of the highest published revision of
    upstream for this suite suffix and prerelease tag, if any."""
    pattern = re.compile(
        rf"{re.escape(upstream)}-(\d+)\+{re.escape(suffix)}"
        + (rf"~{re.escape(tag)}" if tag else "")
    )
    best: Optional[tuple[int, str]] = None
    for name, version in index:
        if name != package:
            continue
        match = pattern.fullmatch(version)
        if match and (best is None or int(match.group(1)) > best[0]):
            best = (int(match.group(1)), version)
    return best


def main() -> int:
    suite = os.environ["SUITE"]
    arch = os.environ["ARCH"]
    apt_host = os.environ["APT_HOST"]
    pkgrev = os.environ.get("PKGREV", "1")
    version = os.environ["VERSION"]
    out_dir = Path(os.environ.get("OUT_DIR", "out"))
    out_dir.mkdir(parents=True, exist_ok=True)

    suffix = SUITE_SUFFIX.get(suite)
    if not suffix:
        log(f"unsupported suite: {suite}")
        return 1

    tag = version.split("~", 1)[1] if "~" in version else ""
    packages_url = f"https://{apt_host}/dists/{suite}/main/binary-{arch}/Packages"

    try:
        index = parse_packages(fetch(packages_url).decode("utf-8", "replace"))
    except Exception as exc:
        log(f"could not read {packages_url}: {exc}; building all packages")
        index = {}

    flags: list[str] = []
    for package, control, flag in PACKAGES:
        control_version = read_control_version(control).split("~", 1)[0]
        expected = f"{control_version}-{pkgrev}+{suffix}"
        if tag:
            expected += f"~{tag}"

        published = find_published(index, package, control_version, suffix, tag)
        entry = index.get((package, expected))
        if published and pkgrev.isdigit() and published[0] >= int(pkgrev):
            # Same upstream version already published at this or a higher
            # revision: reuse it rather than rebuild at a lower revision, which
            # would publish a downgrade.
            if published[1] != expected:
                log(
                    f"{package} {published[1]} published (requested revision "
                    f"{pkgrev}); reusing instead of building {expected}"
                )
            expected = published[1]
            entry = index[(package, expected)]
        if not entry:
            log(f"{package} {expected} not published; will build")
            flags.append(flag)
            continue
        filename, expected_sha256 = entry

        destination = out_dir / Path(filename).name
        if destination.exists():
            log(f"{package} {expected} already present at {destination}")
            continue

        url = f"https://{apt_host}/{filename}"
        log(f"{package} {expected} published; downloading {url}")
        try:
            download(url, destination)
            result = subprocess.run(
                ["dpkg-deb", "-f", str(destination), "Version"],
                capture_output=True,
                text=True,
                check=True,
            )
            actual = result.stdout.strip()
            if actual != expected:
                raise RuntimeError(f"downloaded version {actual} != {expected}")
            actual_sha256 = sha256_file(destination)
            if actual_sha256 != expected_sha256:
                raise RuntimeError(
                    f"downloaded SHA256 {actual_sha256} != index {expected_sha256}"
                )
        except Exception as exc:
            log(f"failed to reuse {package}: {exc}; will build")
            destination.unlink(missing_ok=True)
            flags.append(flag)

    print(" ".join(flags))
    return 0


if __name__ == "__main__":
    sys.exit(main())
