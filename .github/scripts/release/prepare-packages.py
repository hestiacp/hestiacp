#!/usr/bin/env python3
"""Decide which Hestia backend packages need building for one suite/arch.

For each package, compute the exact version the build would produce. If that
version is already published in the channel's apt repo, download the existing
deb into OUT_DIR and skip the build; otherwise emit the matching
hst_autocompile.sh flag.

Build flags are printed on stdout; logs go to stderr.
"""

import os
import shutil
import subprocess
import sys
import urllib.request
from pathlib import Path

# Keep in sync with .github/scripts/release/publish.sh.
SUITE_SUFFIX = {
    "bullseye": "debian11",
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


def parse_packages(text: str) -> dict[tuple[str, str], str]:
    result: dict[tuple[str, str], str] = {}
    for stanza in text.split("\n\n"):
        fields: dict[str, str] = {}
        for line in stanza.splitlines():
            if ": " in line:
                key, value = line.split(": ", 1)
                fields[key] = value
        package = fields.get("Package")
        version = fields.get("Version")
        filename = fields.get("Filename")
        if package and version and filename:
            result[(package, version)] = filename
    return result


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

        filename = index.get((package, expected))
        if not filename:
            log(f"{package} {expected} not published; will build")
            flags.append(flag)
            continue

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
        except Exception as exc:
            log(f"failed to reuse {package}: {exc}; will build")
            destination.unlink(missing_ok=True)
            flags.append(flag)

    print(" ".join(flags))
    return 0


if __name__ == "__main__":
    sys.exit(main())
