#!/usr/bin/env python3
"""Isolated regression tests; no installed Hestia service or real users required.

Run: python3 test/password-upgrade.py
Requires bash, PHP and mkpasswd (whois). All fixtures live below the project's
tmp/ directory and are removed automatically. Absolute system paths in the
disposable command copy are redirected to fixtures; usermod is always mocked.
"""

import json
from pathlib import Path
import shutil
import subprocess
import tempfile
import unittest


ROOT = Path(__file__).resolve().parents[1]
PASSWORD = "Legacy-Pass123!"


def php(code, *args):
    return subprocess.check_output(
        ["php", "-r", code, *args], text=True, timeout=10
    ).strip()


class PasswordUpgrade(unittest.TestCase):
    def setUp(self):
        (ROOT / "tmp").mkdir(exist_ok=True)
        self.temporary = tempfile.TemporaryDirectory(dir=ROOT / "tmp")
        self.addCleanup(self.temporary.cleanup)
        self.home = Path(self.temporary.name)
        self.bin = self.home / "bin"
        self.bin.mkdir()
        (self.home / "conf").mkdir()
        (self.home / "func").mkdir()
        (self.home / "data/users/alice").mkdir(parents=True)
        self.shadow = self.home / "shadow"
        self.original = php(
            "echo crypt($argv[1], '$1$testSalt$');", PASSWORD
        )
        self.assertRegex(self.original, r"^\$1\$testSalt\$[./0-9A-Za-z]{22}$")
        self.set_shadow(self.original)
        self.policy("yes")
        self.executable(
            self.home / "bootstrap",
            f"HESTIA='{self.home}'\n",
        )
        (self.home / "func/main.sh").write_text(
            r"""
BIN=$HESTIA/bin
E_PASSWORD=9
OK=0
check_args() { [ "$2" -ge "$1" ] || exit 1; }
is_format_valid() { [ "$user" = alice ] || exit 2; }
is_object_valid() { [ -d "$HESTIA/data/users/$user" ] || exit 3; }
is_object_unsuspended() { [ ! -f "$HESTIA/suspended" ] || exit 5; }
source_conf() { source "$1"; }
is_password_valid() {
    if [[ "$password" = "$HESTIA/"* ]] && [ -f "$password" ]; then
        password=$(head -n1 "$password")
    fi
}
check_result() { if [ "$1" -ne 0 ]; then echo "Error: $2"; exit "$1"; fi; }
check_hestia_demo_mode() { [ ! -f "$HESTIA/demo" ] || exit 10; }
update_user_value() { printf '%s=%s\n' "$2" "$3" >> "$HESTIA/metadata"; }
generate_password() { echo fresh-reset-token; }
log_event() { echo event >> "$HESTIA/events"; }
"""
        )
        source = (ROOT / "bin/v-upgrade-user-password-hash").read_text()
        source = source.replace("/etc/hestiacp/hestia.conf", str(self.home / "bootstrap"))
        source = source.replace("/etc/shadow", str(self.shadow))
        source = source.replace("/usr/sbin/usermod", str(self.bin / "usermod"))
        self.command = self.home / "upgrade"
        self.executable(self.command, source)
        generator = (ROOT / "bin/v-generate-password-hash").read_text()
        self.executable(
            self.bin / "v-generate-password-hash",
            generator.replace("#!/usr/local/hestia/php/bin/php", "#!/usr/bin/php", 1),
        )
        self.executable(
            self.bin / "usermod",
            """#!/bin/bash
[ ! -f "$HESTIA/fail-write" ] || exit 1
[ "$1" = --password ] && [ "$3" = alice ] || exit 2
printf 'alice:%s:19000:0:99999:7:::\\n' "$2" > "$HESTIA/shadow"
echo write >> "$HESTIA/writes"
""",
        )
        self.executable(
            self.bin / "v-log-action",
            '#!/bin/bash\nprintf "%s\\n" "$*" >> "$HESTIA/actions"\n',
        )
        self.env = {
            "PATH": f"{self.bin}:/usr/bin:/bin",
            "HOME": str(self.home),
            "HESTIA": str(self.home),
            "LC_ALL": "C",
        }

    @staticmethod
    def executable(path, text):
        path.write_text(text)
        path.chmod(0o700)

    def set_shadow(self, value):
        self.shadow.write_text(f"alice:{value}:19000:0:99999:7:::\n")

    def policy(self, value):
        content = "" if value is None else f"POLICY_SYSTEM_UPGRADE_MD5='{value}'\n"
        (self.home / "conf/hestia.conf").write_text(content)

    def run_upgrade(self, password=PASSWORD):
        result = subprocess.run(
            ["bash", str(self.command), "alice", password],
            env=self.env, text=True, capture_output=True, timeout=10,
        )
        self.assertEqual(result.stderr, "")
        return result

    def assert_unchanged(self, expected=None):
        self.assertEqual(self.shadow.read_text().split(":")[1], expected or self.original)
        for name in ["writes", "metadata", "actions", "events"]:
            self.assertFalse((self.home / name).exists(), name)

    def test_disabled_missing_and_unknown_policy(self):
        for policy in ["no", None, "true", "invalid"]:
            with self.subTest(policy=policy):
                self.policy(policy)
                self.assertEqual(self.run_upgrade().returncode, 0)
                self.assert_unchanged()

    def test_success_and_repeat_login(self):
        result = self.run_upgrade()
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        upgraded = self.shadow.read_text().split(":")[1]
        self.assertTrue(upgraded.startswith("$y$"))
        verified = subprocess.check_output(
            ["mkpasswd", "--", PASSWORD, upgraded], text=True, timeout=10
        ).strip()
        self.assertEqual(verified, upgraded)
        self.assertEqual(self.shadow.read_text().split(":")[2], "19000")
        metadata = (self.home / "metadata").read_text()
        self.assertIn(f"$MD5={upgraded}", metadata)
        self.assertIn("$RKEY=fresh-reset-token", metadata)
        self.assertIn("upgraded to yescrypt", (self.home / "actions").read_text())
        self.assertNotIn(PASSWORD, (self.home / "actions").read_text())
        self.assertEqual(self.run_upgrade().returncode, 0)
        self.assertEqual((self.home / "writes").read_text(), "write\n")

    def test_wrong_empty_and_multiline_password(self):
        for password in ["incorrect", "", PASSWORD + "\nextra", PASSWORD + "\r"]:
            with self.subTest(password=repr(password)):
                self.assertEqual(self.run_upgrade(password).returncode, 9)
                self.assert_unchanged()

    def test_password_file_transport(self):
        password_file = self.home / "password-file"
        password_file.write_text(PASSWORD + "\n")
        result = self.run_upgrade(str(password_file))
        self.assertEqual(result.returncode, 0, result.stdout)
        self.assertTrue(self.shadow.read_text().split(":")[1].startswith("$y$"))
        self.assertNotIn(PASSWORD, (self.home / "actions").read_text())

    def test_modern_locked_and_malformed_hashes(self):
        hashes = [
            php("echo crypt($argv[1], '$6$testSalt$');", PASSWORD),
            "$y$j9T$salt$hash", "!" + self.original, "*", "", "$1$salt$bad",
            php("echo crypt($argv[1], 'ab');", PASSWORD),
        ]
        for value in hashes:
            with self.subTest(value=value):
                self.set_shadow(value)
                self.assertEqual(self.run_upgrade().returncode, 0)
                self.assertEqual(self.shadow.read_text().split(":")[1], value)
                self.assertFalse((self.home / "writes").exists())

    def test_suspended_and_demo_accounts(self):
        for marker, status in [("suspended", 5), ("demo", 10)]:
            with self.subTest(marker=marker):
                (self.home / marker).touch()
                self.assertEqual(self.run_upgrade().returncode, status)
                self.assert_unchanged()
                (self.home / marker).unlink()

    def test_write_failure(self):
        (self.home / "fail-write").touch()
        self.assertNotEqual(self.run_upgrade().returncode, 0)
        self.assert_unchanged()

    def test_generator_failure_or_invalid_output(self):
        for script in ["exit 1", "echo '*0'", "echo '$6$salt$hash'", "echo '$y$bad'"]:
            with self.subTest(script=script):
                self.executable(self.bin / "mkpasswd", "#!/bin/bash\n" + script + "\n")
                self.assertNotEqual(self.run_upgrade().returncode, 0)
                self.assert_unchanged()

    def test_concurrent_password_change(self):
        self.executable(
            self.bin / "mkpasswd",
            """#!/bin/bash
echo 'alice:!locked:19000:0:99999:7:::' > "$HESTIA/shadow"
exec /usr/bin/mkpasswd "$@"
""",
        )
        self.assertNotEqual(self.run_upgrade().returncode, 0)
        self.assert_unchanged("!locked")


class PanelLogin(unittest.TestCase):
    """Execute the real authenticate_user function with command/session mocks."""

    def run_login(self, **scenario):
        source = (ROOT / "web/login/index.php").read_text()
        function = source.split('function authenticate_user(', 1)[1].split(
            '\nif (empty($_POST["user"]))', 1
        )[0]
        harness = (ROOT / "test/password-upgrade-login.php").read_text().split("<?php", 1)[1]
        program = harness.replace(
            "// AUTHENTICATE_USER", "function authenticate_user(" + function
        )
        result = subprocess.run(
            ["php", "-r", program, json.dumps(scenario)],
            text=True, capture_output=True, timeout=10,
        )
        self.assertEqual(result.returncode, 0, result.stderr)
        try:
            return json.loads(result.stdout)
        except json.JSONDecodeError:
            self.fail(result.stdout + result.stderr)

    def test_success_upgrades_before_session(self):
        result = self.run_login()
        self.assertEqual(result["upgrades"], 1)
        self.assertEqual(result["user"], "alice")
        self.assertTrue(result["before_session"])

    def test_disabled_policy(self):
        result = self.run_login(policy="no")
        self.assertEqual(result["upgrades"], 0)
        self.assertEqual(result["user"], "alice")

    def test_failed_authentication_never_upgrades(self):
        for scenario in [
            {"csrf": False}, {"password_valid": False}, {"suspended": True},
            {"disabled": True}, {"ip_allowed": False}, {"twofa": ""},
            {"twofa": "654321", "twofa_valid": False}, {"twofa": "1234567890"},
            {"tempfile_valid": False}, {"write_valid": False},
        ]:
            with self.subTest(scenario=scenario):
                result = self.run_login(**scenario)
                self.assertEqual(result["upgrades"], 0)
                self.assertIsNone(result["user"])

    def test_upgrade_failure_never_creates_session(self):
        result = self.run_login(upgrade_valid=False)
        self.assertEqual(result["upgrades"], 1)
        self.assertIsNone(result["user"])

    def test_modern_password_does_not_trigger_upgrade(self):
        result = self.run_login(method="sha-512")
        self.assertEqual(result["upgrades"], 0)
        self.assertEqual(result["user"], "alice")


if __name__ == "__main__":
    if not all(shutil.which(command) for command in ["bash", "php", "mkpasswd"]):
        raise SystemExit("Requires bash, PHP and mkpasswd (whois)")
    unittest.main()
