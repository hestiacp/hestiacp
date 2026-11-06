#!/usr/bin/env python3
"""Write the stanzas for one architecture from an apt-ftparchive Packages file."""

import sys


def main() -> int:
    if len(sys.argv) != 3:
        print(f"usage: {sys.argv[0]} PACKAGES ARCH", file=sys.stderr)
        return 2

    path, arch = sys.argv[1], sys.argv[2]
    with open(path, encoding="utf-8") as handle:
        stanzas = handle.read().split("\n\n")

    for stanza in stanzas:
        if not stanza.strip():
            continue
        fields = stanza.splitlines()
        if any(
            line.strip() in (f"Architecture: {arch}", "Architecture: all")
            for line in fields
        ):
            sys.stdout.write(stanza.rstrip() + "\n\n")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
