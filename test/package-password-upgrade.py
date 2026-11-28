#!/usr/bin/env python3
"""Check an extracted real .deb against its source checkout (no privileged writes).

Usage: python3 test/package-password-upgrade.py EXTRACTED_PACKAGE SOURCE_CHECKOUT
"""
import argparse
import pathlib
import stat
import sys
import unittest


payload = None
source = None
helpers = (
    "bin/v-change-sys-mail-password-upgrade",
    "bin/v-upgrade-user-password-hash",
    "func/dovecot-password-upgrade.lua",
    "func/dovecot-password-upgrade.php",
    "func/mail-password-lock.sh",
    "install/common/dovecot/password-upgrade.service.conf",
    "install/common/dovecot/2.3/auth-password-upgrade.conf.ext",
    "install/common/dovecot/2.4/auth-password-upgrade.conf.ext",
)
modified = (
    "bin/v-list-sys-config",
    "func/main.sh",
    "func/syshealth.sh",
    "install/common/dovecot/2.3/conf.d/auth-passwdfile.conf.ext",
    "install/common/dovecot/2.4/conf.d/auth-passwdfile.conf.ext",
    "install/hst-install-debian.sh",
    "install/hst-install-ubuntu.sh",
    "web/edit/server/index.php",
    "web/login/index.php",
    "web/templates/pages/edit_server.php",
)


class PackagedPasswordUpgrade(unittest.TestCase):
    def test_helpers_and_integrations_match_source(self):
        for name in helpers + modified:
            with self.subTest(path=name):
                target = payload / name
                self.assertTrue(target.is_file())
                self.assertFalse(target.is_symlink())
                self.assertEqual(target.read_bytes(), (source / name).read_bytes())

    def test_modes(self):
        for name in helpers:
            with self.subTest(path=name):
                mode = (payload / name).stat().st_mode
                self.assertEqual(bool(mode & stat.S_IXUSR), name.startswith("bin/"))
                self.assertFalse(mode & (stat.S_IWGRP | stat.S_IWOTH))

    def test_no_test_or_working_files(self):
        self.assertEqual(
            {p.name for p in payload.iterdir()}, {"bin", "func", "install", "web"}
        )
        for path in payload.rglob("*"):
            self.assertNotIn(path.name, {
                "mail-password-upgrade-barrier.php",
                "mail-password-upgrade-fixture.php",
                "password-upgrade-login.php",
                "node_modules",
                ".git",
            })

    def test_frontend_really_built(self):
        for name in ("web/js/dist/main.min.js", "web/css/themes/default.min.css"):
            self.assertGreater((payload / name).stat().st_size, 1000)


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("extracted_package", type=pathlib.Path)
    parser.add_argument("source_checkout", type=pathlib.Path)
    args, remaining = parser.parse_known_args()
    payload = args.extracted_package.resolve() / "usr/local/hestia"
    source = args.source_checkout.resolve()
    unittest.main(argv=[sys.argv[0], *remaining])
