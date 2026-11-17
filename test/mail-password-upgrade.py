#!/usr/bin/env python3
"""Mailbox migration regression tests; never operates on real mail accounts.

Run: python3 test/mail-password-upgrade.py
Uses PHP, bash, and optionally liblua5.4. The daemon/config commands and all
system paths are redirected to temporary fixtures below the project's tmp/.
Native Dovecot configuration/authentication still needs separate integration
validation on Dovecot 2.3 and 2.4 before release.
"""

import ctypes
import fcntl
import json
from pathlib import Path
import shutil
import subprocess
import tempfile
import unittest

ROOT = Path(__file__).resolve().parents[1]


class FixtureTest(unittest.TestCase):
    def setUp(self):
        (ROOT / "tmp").mkdir(exist_ok=True)
        self.temporary = tempfile.TemporaryDirectory(dir=ROOT / "tmp")
        self.addCleanup(self.temporary.cleanup)
        self.home = Path(self.temporary.name)


class Migration(FixtureTest):
    def migrate(self, **scenario):
        result = subprocess.run(
            ["php", str(ROOT / "test/mail-password-upgrade-fixture.php"),
             str(self.home), json.dumps(scenario)],
            text=True, capture_output=True, timeout=15,
        )
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        self.assertEqual(result.stderr, "")
        data = json.loads(result.stdout)
        self.assertEqual(data["staged_files"], [])
        return data

    def assert_no_upgrade(self, data, status):
        self.assertEqual(data["status"], status)
        self.assertTrue(data["metadata_unchanged"])
        self.assertTrue(data["runtime_unchanged"])
        self.assertTrue(data["other_preserved"])

    def test_success_and_repeat(self):
        data = self.migrate(schemes="BLF-CRYPT", repeat=True)
        self.assertEqual(data["status"], 0)
        self.assertEqual(data["again"], 10)
        self.assertTrue(data["runtime_hash"].startswith("{BLF-CRYPT}$2y$"))
        self.assertEqual(data["runtime_hash"], data["cache_hash"])
        self.assertTrue(data["password_matches"])
        self.assertTrue(data["other_preserved"])
        self.assertTrue(data["permissions_preserved"])
        self.assertEqual(data["probe_args"], "-O pw -l")

    def test_argon2id_preferred(self):
        data = self.migrate()
        self.assertEqual(data["status"], 0)
        self.assertTrue(data["runtime_hash"].startswith("{ARGON2ID}$argon2id$"))
        self.assertEqual(data["runtime_hash"], data["cache_hash"])
        self.assertTrue(data["password_matches"])

    def test_account_name_suffix_is_not_modified(self):
        data = self.migrate(suffix_account=True, schemes="BLF-CRYPT")
        self.assertEqual(data["status"], 0)
        self.assertTrue(data["suffix_preserved"])

    def test_disabled(self):
        self.assert_no_upgrade(self.migrate(policy="no"), 10)

    def test_missing_policy(self):
        self.assert_no_upgrade(self.migrate(missing=True), 10)

    def test_demo_mode(self):
        self.assert_no_upgrade(self.migrate(demo=True), 10)

    def test_wrong_password(self):
        self.assert_no_upgrade(self.migrate(wrong=True), 1)

    def test_suspended_user(self):
        self.assert_no_upgrade(self.migrate(user_suspended=True), 1)

    def test_suspended_domain(self):
        self.assert_no_upgrade(self.migrate(domain_suspended=True), 1)

    def test_suspended_mailbox(self):
        self.assert_no_upgrade(self.migrate(account_suspended=True), 1)

    def test_modern_hash_unchanged(self):
        self.assert_no_upgrade(self.migrate(modern=True), 10)

    def test_locked_runtime_unchanged(self):
        self.assert_no_upgrade(self.migrate(locked=True), 10)

    def test_duplicate_runtime_account(self):
        self.assert_no_upgrade(self.migrate(duplicate=True), 1)

    def test_md5_crypt_prefix(self):
        self.assertEqual(self.migrate(prefix="{MD5-CRYPT}", schemes="BLF-CRYPT")["status"], 0)

    def test_crypt_prefix(self):
        self.assertEqual(self.migrate(prefix="{CRYPT}", schemes="BLF-CRYPT")["status"], 0)

    def test_unprefixed_legacy(self):
        self.assertEqual(self.migrate(prefix="", schemes="BLF-CRYPT")["status"], 0)

    def test_no_weak_fallback(self):
        self.assert_no_upgrade(self.migrate(schemes="MD5-CRYPT"), 111)

    def test_no_bcrypt_truncation(self):
        self.assert_no_upgrade(self.migrate(long=True, schemes="BLF-CRYPT"), 111)

    def test_long_password_argon2id(self):
        data = self.migrate(long=True)
        self.assertEqual(data["status"], 0)
        self.assertTrue(data["password_matches"])

    def test_partial_commit_recovery(self):
        data = self.migrate(partial=True)
        self.assertEqual(data["status"], 0)
        self.assertTrue(data["metadata_unchanged"])
        self.assertEqual(data["cache_hash"], data["runtime_hash"])
        self.assertTrue(data["password_matches"])

    def test_reset_password_is_not_overwritten(self):
        self.assert_no_upgrade(self.migrate(cache_reset=True), 1)

    def test_concurrent_reset_is_not_overwritten(self):
        data = self.migrate(race=True, schemes="BLF-CRYPT")
        self.assertEqual(data["status"], 111)
        self.assertTrue(data["runtime_unchanged"])
        self.assertIn("reset-by-admin", data["metadata"])

    def test_policy_disabled_during_hashing(self):
        data = self.migrate(disable_during_hash=True, schemes="BLF-CRYPT")
        self.assert_no_upgrade(data, 111)
        self.assertIn("POLICY_MAIL_UPGRADE_MD5='no'", data["policy"])

    def test_metadata_symlink(self):
        self.assert_no_upgrade(self.migrate(metadata_symlink=True), 111)

    def test_passwd_symlink(self):
        self.assert_no_upgrade(self.migrate(passwd_symlink=True), 1)

    def test_hardlinked_metadata(self):
        self.assert_no_upgrade(self.migrate(hardlink=True), 111)

    def test_symlinked_lock(self):
        data = self.migrate(lock_symlink=True)
        self.assert_no_upgrade(data, 111)
        self.assertEqual(data["outside"], "do-not-touch")

    def test_symlinked_commit_lock(self):
        data = self.migrate(commit_lock_symlink=True)
        self.assert_no_upgrade(data, 111)
        self.assertEqual(data["outside"], "do-not-touch")

    def test_hardlinked_commit_lock(self):
        data = self.migrate(commit_lock_hardlink=True)
        self.assert_no_upgrade(data, 111)
        self.assertEqual(data["outside"], "do-not-touch")

    def test_busy_administrative_lock(self):
        self.assert_no_upgrade(self.migrate(commit_lock_busy=True), 111)

    def test_runtime_owner_mismatch(self):
        self.assert_no_upgrade(self.migrate(owner_mismatch=True), 1)

    def test_traversal(self):
        self.assert_no_upgrade(self.migrate(email="bob@../example.com"), 1)

    def test_probe_failure(self):
        self.assert_no_upgrade(self.migrate(probe_fail=True), 111)


class AdministrativeGate(FixtureTest):
    def setUp(self):
        super().setUp()
        (self.home / "data").mkdir()
        self.gate = self.home / "data/.mail-password-upgrade.lock"
        self.library = self.home / "func/mail-password-lock.sh"
        self.library.parent.mkdir()
        # Keep a lock-contention regression bounded to a fraction of a second.
        self.library.write_text((ROOT / "func/mail-password-lock.sh").read_text().replace(
            "--timeout 30", "--timeout 0.2"))
        self.script = """
HESTIA=$1
E_PARSING=12
check_result() { if [ "$1" -ne 0 ]; then exit "$1"; fi; }
source "$2"
lock_mail_password_writes
echo acquired
"""

    def acquire(self, script=None):
        return subprocess.run(
            ["bash", "-s", "--", str(self.home), str(self.library)],
            input=script or self.script, text=True, capture_output=True, timeout=5)

    def test_shared_gate_allows_nested_writers_and_releases_on_exit(self):
        script = self.script + """
export HESTIA
bash -c 'E_PARSING=12; check_result() { [ "$1" -eq 0 ] || exit "$1"; }; source "$1"; lock_mail_password_writes; echo nested' bash "$2"
"""
        result = self.acquire(script)
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertEqual(result.stdout, "acquired\nnested\n")
        with self.gate.open("a") as lock:
            fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)

    def test_writer_cannot_bypass_exclusive_commit(self):
        with self.gate.open("a") as lock:
            fcntl.flock(lock, fcntl.LOCK_EX)
            result = self.acquire()
        self.assertNotEqual(result.returncode, 0)
        self.assertNotIn("acquired", result.stdout)

    def test_symlink_rejected_without_changing_target(self):
        target = self.home / "outside"
        target.write_text("untouched")
        target.chmod(0o640)
        self.gate.symlink_to(target)
        self.assertEqual(self.acquire().returncode, 12)
        self.assertEqual(target.read_text(), "untouched")
        self.assertEqual(target.stat().st_mode & 0o777, 0o640)

    def test_hardlink_rejected_without_changing_target(self):
        target = self.home / "outside"
        target.write_text("untouched")
        self.gate.hardlink_to(target)
        self.assertEqual(self.acquire().returncode, 12)
        self.assertEqual(target.read_text(), "untouched")

    def test_main_gates_all_mail_lifecycle_entrypoints(self):
        names = [
            "v-add-mail-account", "v-delete-mail-account", "v-change-mail-account-quota",
            "v-change-mail-account-password", "v-suspend-mail-account",
            "v-unsuspend-mail-account", "v-suspend-mail-domain", "v-unsuspend-mail-domain",
            "v-delete-mail-domain", "v-rebuild-mail-domain", "v-rebuild-mail-domains",
            "v-restore-user", "v-restore-mail-domain-restic", "v-import-cpanel",
            "v-import-directadmin", "v-change-domain-owner", "v-delete-user",
            "v-suspend-user", "v-unsuspend-user", "v-change-sys-config-value",
            "v-change-sys-mail-password-upgrade",
        ]
        for name in names:
            with self.subTest(command=name):
                entrypoint = self.home / name
                entrypoint.write_text('HESTIA=$1; user=alice; source "$2"; echo acquired\n')
                result = subprocess.run(
                    ["bash", str(entrypoint), str(self.home), str(ROOT / "func/main.sh")],
                    text=True, capture_output=True, timeout=5)
                self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
                self.assertIn("acquired", result.stdout)
                self.assertTrue(self.gate.is_file())
                self.assertEqual(self.gate.stat().st_mode & 0o777, 0o600)
                self.gate.unlink()

    def test_missing_library_fails_closed_before_writer_body(self):
        self.library.unlink()
        (self.home / "log").mkdir()
        entrypoint = self.home / "v-change-mail-account-password"
        entrypoint.write_text('HESTIA=$1; user=alice; source "$2"; echo acquired\n')
        result = subprocess.run(
            ["bash", str(entrypoint), str(self.home), str(ROOT / "func/main.sh")],
            text=True, capture_output=True, timeout=5)
        self.assertEqual(result.returncode, 12)
        self.assertNotIn("acquired", result.stdout)


class Configuration(FixtureTest):
    def setUp(self):
        super().setUp()
        self.bin = self.home / "bin"
        self.bin.mkdir()
        for directory in ["func", "conf", "dovecot/conf.d", "modules/auth", "libexec", "tmp"]:
            (self.home / directory).mkdir(parents=True)
        self.lua_module = self.home / "modules/auth/libauthdb_lua.so"
        self.lua_module.touch()
        (self.home / "libexec/auth").write_text("no built-in Lua support")
        for version in ["2.3", "2.4"]:
            path = f"install/common/dovecot/{version}/auth-password-upgrade.conf.ext"
            (self.home / path).parent.mkdir(parents=True)
            shutil.copy(ROOT / path, self.home / path)
        for suffix in ["lua", "php"]:
            (self.home / f"func/dovecot-password-upgrade.{suffix}").touch()
        shutil.copy(ROOT / "install/common/dovecot/password-upgrade.service.conf",
                    self.home / "install/common/dovecot/password-upgrade.service.conf")
        self.unit = self.home / "systemd/dovecot.service.d/hestia-password-upgrade.conf"
        self.config = self.home / "conf/hestia.conf"
        self.original = "IMAP_SYSTEM='dovecot'\nMAIL_SYSTEM='exim4'\nPOLICY_MAIL_UPGRADE_MD5='no'\n"
        self.config.write_text(self.original)
        self.auth = self.home / "dovecot/conf.d/auth-passwdfile.conf.ext"
        self.auth.write_text("passdb passwd-file {\n  driver = passwd-file\n}\n")
        self.original_auth = self.auth.read_text()
        self.hook = self.home / "dovecot/conf.d/auth-hestia-password-upgrade.conf.ext"
        (self.home / "bootstrap").write_text(f"HESTIA='{self.home}'\n")
        (self.home / "func/main.sh").write_text("""
BIN=$HESTIA/bin
HESTIA_PHP=/usr/bin/php
OK=0
E_DISABLED=11
E_INVALID=2
E_NOTEXIST=3
E_PARSING=12
E_RESTART=20
source_conf() { source "$1"; }
check_args() { [ "$2" -ge "$1" ] || exit 1; }
check_result() { if [ "$1" -ne 0 ]; then echo "Error: $2"; exit "$1"; fi; }
is_boolean_format_valid() { [[ "$1" = yes || "$1" = no ]] || exit 2; }
check_hestia_demo_mode() { [ ! -f "$HESTIA/demo" ] || exit 10; }
log_event() { :; }
""")
        self.executable(self.bin / "dovecot", """#!/bin/bash
if [ "$1" = --version ]; then cat "$HESTIA/version"; else cat "$HESTIA/build"; fi
""")
        self.executable(self.bin / "doveconf", """#!/bin/bash
case "$*" in
    "-h mail_plugin_dir") printf '%s\\n' "$HESTIA/modules" ;;
    "-h libexec_dir") printf '%s\\n' "$HESTIA/libexec" ;;
    "-n") [ ! -f "$HESTIA/fail-validation" ] ;;
    *) exit 2 ;;
esac
""")
        self.executable(self.bin / "v-change-sys-config-value", """#!/bin/bash
if [ -f "$HESTIA/fail-save" ]; then rm "$HESTIA/fail-save"; exit 1; fi
printf "IMAP_SYSTEM='dovecot'\\nMAIL_SYSTEM='exim4'\\nPOLICY_MAIL_UPGRADE_MD5='%s'\\n" "$2" > "$HESTIA/conf/hestia.conf"
""")
        self.executable(self.bin / "systemctl", """#!/bin/bash
if [ "$*" = daemon-reload ]; then
    echo reload >> "$HESTIA/daemon-reloads"
    if [ -f "$HESTIA/fail-daemon-reload" ]; then rm "$HESTIA/fail-daemon-reload"; exit 1; fi
    exit 0
fi
[ "$*" = 'restart dovecot' ] || exit 2
echo restart >> "$HESTIA/restarts"
if [ -f "$HESTIA/fail-reload" ]; then rm "$HESTIA/fail-reload"; exit 1; fi
""")
        self.executable(self.bin / "v-log-action", "#!/bin/bash\nexit 0\n")
        (self.home / "version").write_text("2.4.1\n")
        (self.home / "build").write_text("Passdb: pam passwd passwd-file sql\n")
        source = (ROOT / "bin/v-change-sys-mail-password-upgrade").read_text()
        source = source.replace("/etc/hestiacp/hestia.conf", str(self.home / "bootstrap"))
        source = source.replace("/etc/dovecot", str(self.home / "dovecot"))
        source = source.replace("/etc/systemd/system", str(self.home / "systemd"))
        self.command = self.home / "configure"
        self.executable(self.command, source)
        self.env = {
            "PATH": f"{self.bin}:/usr/bin:/bin", "HESTIA": str(self.home),
            "HOME": str(self.home), "TMPDIR": str(self.home / "tmp"), "LC_ALL": "C",
        }

    @staticmethod
    def executable(path, content):
        path.write_text(content)
        path.chmod(0o700)

    def configure(self, value):
        return subprocess.run(
            ["bash", str(self.command), value],
            text=True, capture_output=True, env=self.env, timeout=15,
        )

    def assert_restored(self):
        self.assertEqual(self.config.read_text(), self.original)
        self.assertEqual(self.auth.read_text(), self.original_auth)
        self.assertFalse(self.hook.exists())
        self.assertFalse(self.unit.exists())
        self.assertEqual(list((self.home / "tmp").iterdir()), [])

    def test_enable_disable(self):
        result = self.configure("yes")
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        self.assertIn("lua_file", self.hook.read_text())
        self.assertNotIn("auth_allow_weak_schemes", self.hook.read_text())
        self.assertIn("POLICY_MAIL_UPGRADE_MD5='yes'", self.config.read_text())
        self.assertTrue(self.auth.read_text().startswith("!include_try "))
        settings = [line for line in self.unit.read_text().splitlines()
                    if line and not line.startswith("#")]
        self.assertEqual(settings, [
            "[Service]",
            "ReadWritePaths=/usr/local/hestia/data/users /usr/local/hestia/data/.mail-password-upgrade.lock",
        ])
        self.assertEqual(self.configure("yes").returncode, 0)
        self.assertEqual(self.auth.read_text().count("!include_try"), 1)
        self.assertEqual(self.configure("no").returncode, 0)
        self.assertFalse(self.hook.exists())
        self.assertFalse(self.unit.exists())
        self.assertEqual(self.config.read_text(), self.original)

    def test_dovecot23(self):
        (self.home / "version").write_text("2.3.21\n")
        self.assertEqual(self.configure("yes").returncode, 0)
        self.assertIn("args = file=", self.hook.read_text())

    def test_lua_required(self):
        self.lua_module.unlink()
        self.assertEqual(self.configure("yes").returncode, 11)
        self.assert_restored()

    def test_systemd_template_required(self):
        (self.home / "install/common/dovecot/password-upgrade.service.conf").unlink()
        self.assertEqual(self.configure("yes").returncode, 3)
        self.assert_restored()

    def test_builtin_lua_without_plugin(self):
        self.lua_module.unlink()
        (self.home / "libexec/auth").write_bytes(b"dummy\0auth_password_verify\0")
        self.assertEqual(self.configure("yes").returncode, 0)

    def test_disable_without_lua(self):
        self.lua_module.unlink()
        self.assertEqual(self.configure("no").returncode, 0)
        self.assertEqual(self.config.read_text(), self.original)
        self.assertFalse(self.hook.exists())

    def test_unknown_version(self):
        (self.home / "version").write_text("2.2.99\n")
        self.assertEqual(self.configure("yes").returncode, 2)
        self.assert_restored()

    def test_validation_rollback(self):
        (self.home / "fail-validation").touch()
        self.assertEqual(self.configure("yes").returncode, 12)
        self.assert_restored()
        self.assertFalse((self.home / "restarts").exists())

    def test_save_rollback(self):
        (self.home / "fail-save").touch()
        self.assertEqual(self.configure("yes").returncode, 12)
        self.assert_restored()

    def test_reload_rollback(self):
        (self.home / "fail-reload").touch()
        self.assertEqual(self.configure("yes").returncode, 20)
        self.assert_restored()
        self.assertEqual((self.home / "restarts").read_text(), "restart\nrestart\n")

    def test_daemon_reload_rollback(self):
        (self.home / "fail-daemon-reload").touch()
        self.assertEqual(self.configure("yes").returncode, 20)
        self.assert_restored()
        self.assertEqual((self.home / "daemon-reloads").read_text(), "reload\nreload\n")
        self.assertFalse((self.home / "restarts").exists())

    def test_existing_unit_restored(self):
        self.unit.parent.mkdir(parents=True)
        self.unit.write_text("# existing managed configuration\n[Service]\n")
        (self.home / "fail-reload").touch()
        self.assertEqual(self.configure("yes").returncode, 20)
        self.assertEqual(self.unit.read_text(), "# existing managed configuration\n[Service]\n")
        self.assertEqual(self.config.read_text(), self.original)

    def test_disable_rollback(self):
        self.assertEqual(self.configure("yes").returncode, 0)
        enabled_auth = self.auth.read_text()
        enabled_hook = self.hook.read_text()
        enabled_unit = self.unit.read_text()
        (self.home / "fail-reload").touch()
        self.assertEqual(self.configure("no").returncode, 20)
        self.assertIn("POLICY_MAIL_UPGRADE_MD5='yes'", self.config.read_text())
        self.assertEqual(self.auth.read_text(), enabled_auth)
        self.assertEqual(self.hook.read_text(), enabled_hook)
        self.assertEqual(self.unit.read_text(), enabled_unit)

    def test_disable_daemon_reload_rollback(self):
        self.assertEqual(self.configure("yes").returncode, 0)
        enabled_unit = self.unit.read_text()
        (self.home / "fail-daemon-reload").touch()
        self.assertEqual(self.configure("no").returncode, 20)
        self.assertIn("POLICY_MAIL_UPGRADE_MD5='yes'", self.config.read_text())
        self.assertTrue(self.hook.exists())
        self.assertEqual(self.unit.read_text(), enabled_unit)

    def test_demo(self):
        (self.home / "demo").touch()
        self.assertEqual(self.configure("yes").returncode, 10)
        self.assert_restored()

    def test_invalid_value(self):
        self.assertEqual(self.configure("true").returncode, 2)
        self.assert_restored()


class InstallerPackages(FixtureTest):
    def test_optional_lua_package_selection(self):
        fake_bin = self.home / "bin"
        fake_bin.mkdir()
        apt_cache = fake_bin / "apt-cache"
        for distribution in ["debian", "ubuntu"]:
            installer = ROOT / f"install/hst-install-{distribution}.sh"
            source = installer.read_text()
            start = source.index("#                     Package Includes ")
            end = source.index("#                     Package Excludes ", start)
            # Exercise only the package-selection block, NEVER the installer.
            block = source[start:end]
            for available in [True, False]:
                apt_cache.write_text(
                    '#!/bin/bash\n[ "$*" = "show dovecot-auth-lua" ] || exit 99\n'
                    f"exit {0 if available else 100}\n"
                )
                apt_cache.chmod(0o700)
                for exim in ["yes", "no"]:
                    for dovecot in ["yes", "no"]:
                        with self.subTest(distribution=distribution, available=available,
                                          exim=exim, dovecot=dovecot):
                            result = subprocess.run(
                                ["bash", "-eu", "-c", block + '\nprintf "%s" "$software"\n'],
                                env={"PATH": f"{fake_bin}:/usr/bin:/bin",
                                     "HOME": str(self.home), "TMPDIR": str(self.home),
                                     "LC_ALL": "C", "phpfpm": "no", "software": "base",
                                     "exim": exim, "dovecot": dovecot},
                                text=True, capture_output=True, timeout=5,
                            )
                            self.assertEqual(result.returncode, 0, result.stderr)
                            expected = available and exim == "yes" and dovecot == "yes"
                            self.assertEqual("dovecot-auth-lua" in result.stdout.split(), expected)


class HelperFixture(FixtureTest):
    def setUp(self):
        super().setUp()
        (self.home / "conf").mkdir()
        (self.home / "func").mkdir()
        (self.home / "conf/hestia.conf").write_text("IMAP_SYSTEM='dovecot'\n")
        self.helper = self.home / "func/dovecot-password-upgrade.php"
        shutil.copy(ROOT / "func/dovecot-password-upgrade.php", self.helper)


class InputProtocol(HelperFixture):
    def test_bounded_stdin_protocol(self):
        for payload, expected in [
            ("bob@example.com\nLegacy-Mail123!\n", 10),
            ("", 1), ("bob@example.com\n", 1),
            ("bob@example.com\npassword\nunexpected\n", 1),
            ("x" * 321 + "\npassword\n", 1),
            ("bob@example.com\n" + "x" * 4097 + "\n", 1),
        ]:
            with self.subTest(expected=expected, size=len(payload)):
                result = subprocess.run(
                    ["php", str(self.helper)], input=payload,
                    text=True, capture_output=True, timeout=5,
                )
                self.assertEqual(result.returncode, expected, result.stdout + result.stderr)
                self.assertEqual(result.stdout, "")
                self.assertEqual(result.stderr, "")


class LuaProtocol(HelperFixture):
    def run_lua(self, program):
        try:
            lua = ctypes.CDLL("liblua5.4.so.0")
        except OSError:
            self.skipTest("liblua5.4 unavailable")
        lua.luaL_newstate.restype = ctypes.c_void_p
        lua.luaL_openlibs.argtypes = [ctypes.c_void_p]
        lua.luaL_loadstring.argtypes = [ctypes.c_void_p, ctypes.c_char_p]
        lua.lua_pcallk.argtypes = [ctypes.c_void_p, ctypes.c_int, ctypes.c_int,
                                 ctypes.c_int, ctypes.c_longlong, ctypes.c_void_p]
        lua.lua_tolstring.argtypes = [ctypes.c_void_p, ctypes.c_int, ctypes.c_void_p]
        lua.lua_tolstring.restype = ctypes.c_char_p
        lua.lua_close.argtypes = [ctypes.c_void_p]
        state = lua.luaL_newstate()
        try:
            lua.luaL_openlibs(state)
            status = lua.luaL_loadstring(state, program.encode())
            if status == 0:
                status = lua.lua_pcallk(state, 0, 0, 0, 0, None)
            error = lua.lua_tolstring(state, -1, None) if status else None
            self.assertEqual(status, 0, error)
        finally:
            lua.lua_close(state)

    def test_real_pipe_to_disabled_helper(self):
        script = (ROOT / "func/dovecot-password-upgrade.lua").read_text()
        script = script.replace("/usr/local/hestia/php/bin/php", "/usr/bin/php")
        script = script.replace("/usr/local/hestia/func/dovecot-password-upgrade.php", str(self.helper))
        self.run_lua("""
dovecot = {auth = {PASSDB_RESULT_NEXT=10, PASSDB_RESULT_PASSWORD_MISMATCH=1,
    PASSDB_RESULT_INTERNAL_FAILURE=111}}
""" + script + """
assert(auth_password_verify({user="bob@example.com", mech="PLAIN"}, "Legacy-Mail123!") == 10)
""")

    def test_preflight_always_defers_final_authentication(self):
        script = (ROOT / "func/dovecot-password-upgrade.lua").read_text()
        harness = r'''
dovecot = {auth = {
    PASSDB_RESULT_NEXT = 10, PASSDB_RESULT_PASSWORD_MISMATCH = 1,
    PASSDB_RESULT_INTERNAL_FAILURE = 111
}}
local closed, written, called = 0, "", 0
local helper_status, allocate, write_ok = 0, true, true
io.popen = function(command, mode)
    called = called + 1
    assert(not command:find("Legacy%-Mail123!"))
    assert(not command:find("bob@example.com", 1, true))
    assert(mode == "w")
    if not allocate then return nil end
    return {
        write = function(self, ...)
            written = table.concat({...})
            return write_ok and self or nil
        end,
        close = function()
            closed = closed + 1
            if helper_status == 0 then return true, "exit", 0 end
            return nil, "exit", helper_status
        end
    }
end
'''
        checks = r'''
-- Dovecot 2.4 rejects a table for any non-OK result, including NEXT.
local verify = auth_password_verify
auth_password_verify = function(...)
    local result, message = verify(...)
    assert(type(result) == "number" and type(message) == "string")
    return result, message
end
local req = {user = "bob@example.com", mech = "PLAIN"}
for _, scenario in ipairs({{0,10}, {10,10}, {1,1}, {111,111}, {124,111}}) do
    helper_status = scenario[1]
    assert(auth_password_verify(req, "Legacy-Mail123!") == scenario[2])
    assert(written == "bob@example.com\nLegacy-Mail123!\n")
end
assert(closed == 5)
helper_status = 0
assert(auth_password_verify({user=req.user, mechanism="PLAIN"}, "Legacy-Mail123!") == 10)
allocate = false
assert(auth_password_verify(req, "Legacy-Mail123!") == 111)
allocate, write_ok, helper_status = true, false, 0
assert(auth_password_verify(req, "Legacy-Mail123!") == 111)
local previous = called
assert(auth_password_verify({user=req.user, mech="CRAM-MD5"}, "x") == 10)
assert(auth_password_verify({user=req.user, mech="PLAIN", master_user="admin"}, "x") == 10)
assert(auth_password_verify(req, "") == 1)
assert(auth_password_verify(req, "x\nextra") == 1)
assert(auth_password_verify(req, string.rep("x", 4097)) == 1)
local lookup_result, lookup_message = auth_passdb_lookup(req)
assert(lookup_result == 10 and type(lookup_message) == "string")
assert(auth_passdb_get_cache_key() == "")
assert(called == previous)
'''
        self.run_lua(harness + script + checks)


if __name__ == "__main__":
    if not all(shutil.which(command) for command in ["php", "bash"]):
        raise SystemExit("Requires PHP and bash")
    unittest.main()
