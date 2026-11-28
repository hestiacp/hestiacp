#!/usr/bin/env python3
"""Native Dovecot 2.3/2.4 tests, ONLY in the disposable password-test sandbox.

Launch: bash test/mail-password-upgrade-native-sandbox.sh (disposable host only).

Needs the existing Dovecot Lua plugin, IMAP/POP3 daemons, Exim, and Hestia PHP.
The launcher must supply read-only source/toolchain, private mount/PID/network
namespaces, scratch-backed system paths, and resource limits. Never run against
a host installation. No real users, mailboxes, credentials, or TLS keys are used.
"""

import base64
import fcntl
import imaplib
import os
from pathlib import Path
import poplib
import re
import shutil
import smtplib
import socket
import ssl
import subprocess
import time
import unittest

SCRATCH = Path("/scratch")
HESTIA = Path("/usr/local/hestia")
SOURCE = Path("/work")
PHP = str(HESTIA / "php/bin/php")
PASSWORD = "Disposable-Mail-123!"
MARKER = SCRATCH / "password-native-sandbox"


def run(args, **kwargs):
    return subprocess.run(args, text=True, capture_output=True, timeout=20, **kwargs)


class NativeAuthentication(unittest.TestCase):
    serial = 0

    @classmethod
    def setUpClass(cls):
        if not MARKER.is_file() or MARKER.read_text() != "isolated-synthetic-fixture\n":
            raise RuntimeError("Refusing to run outside the disposable password-test sandbox")
        version = run(["dovecot", "--version"])
        cls.version = version.stdout.split()[0]
        cls.dovecot24 = cls.version.startswith("2.4.")
        if not cls.dovecot24 and not cls.version.startswith("2.3."):
            raise RuntimeError("This native fixture requires Dovecot 2.3/2.4")
        print("Native fixture Dovecot " + cls.version, flush=True)
        for path in [HESTIA / "conf", HESTIA / "data/users/alice/mail",
                     Path("/etc/dovecot/conf.d"), Path("/etc/exim4/domains"),
                     Path("/home/alice/conf/mail"), SCRATCH / "home"]:
            path.mkdir(parents=True, exist_ok=True)
        cls.policy("yes")
        (HESTIA / "data/users/alice/user.conf").write_text("USER='alice'\nSUSPENDED='no'\n")
        (HESTIA / "data/users/alice/mail.conf").write_text("")
        result = run([PHP, "-r", "echo crypt(stream_get_contents(STDIN), '$1$fixture$');"],
                     input=PASSWORD)
        if result.returncode or not result.stdout.startswith("$1$"):
            raise RuntimeError("Native PHP cannot generate the synthetic MD5-crypt fixture")
        cls.legacy = "{MD5}" + result.stdout
        result = run([PHP, "-r", "echo defined('PASSWORD_ARGON2ID') ? 'ARGON2ID' : 'BLF-CRYPT';"])
        cls.expected_scheme = "{" + result.stdout + "}"
        branch = "2.4" if cls.dovecot24 else "2.3"
        shutil.copy(SOURCE / f"install/common/dovecot/{branch}/conf.d/auth-passwdfile.conf.ext",
                    "/etc/dovecot/conf.d/auth-passwdfile.conf.ext")
        shutil.copy(SOURCE / f"install/common/dovecot/{branch}/auth-password-upgrade.conf.ext",
                    "/etc/dovecot/conf.d/auth-hestia-password-upgrade.conf.ext")
        result = run(["openssl", "req", "-x509", "-newkey", "rsa:2048", "-noenc",
                      "-keyout", "/scratch/key.pem", "-out", "/scratch/cert.pem",
                      "-days", "1", "-subj", "/CN=password-test.invalid"])
        if result.returncode:
            raise RuntimeError(result.stderr)
        os.chown("/scratch/key.pem", 0, 8)
        os.chmod("/scratch/key.pem", 0o640)
        cls.dovecot_config = Path("/etc/dovecot/dovecot.conf")
        config = """
dovecot_config_version = 2.4.0
dovecot_storage_version = 2.4.0
protocols = imap pop3
base_dir = /run/dovecot
state_dir = /var/lib/dovecot
instance_name = password-native-fixture
listen = 127.0.0.1
log_path = /scratch/dovecot.log
info_log_path = /scratch/dovecot.log
auth_mechanisms = plain login
auth_username_format = %{user | lower}
auth_allow_cleartext = no
auth_failure_delay = 0
auth_cache_size = 0
log_debug = category=auth
ssl = required
ssl_server_cert_file = /scratch/cert.pem
ssl_server_key_file = /scratch/key.pem
mail_driver = maildir
mail_home = ~
mail_path = ~
namespace inbox {
  inbox = yes
  separator = /
}
service auth {
  user = dovecot
  extra_groups = mail
  unix_listener auth-client {
    user = dovecot
    group = mail
    mode = 0660
  }
}
service imap-login {
  inet_listener imap {
    port = 0
  }
  inet_listener imaps {
    port = 1993
    ssl = yes
  }
}
service pop3-login {
  inet_listener pop3 {
    port = 0
  }
  inet_listener pop3s {
    port = 1995
    ssl = yes
  }
}
!include conf.d/auth-passwdfile.conf.ext
"""
        if not cls.dovecot24:
            config = config.replace("dovecot_config_version = 2.4.0\n", "")
            config = config.replace("dovecot_storage_version = 2.4.0\n", "")
            config = config.replace("auth_username_format = %{user | lower}",
                                    "auth_username_format = %Lu")
            config = config.replace("auth_allow_cleartext = no", "disable_plaintext_auth = yes")
            config = config.replace("log_debug = category=auth", "auth_debug = yes")
            config = config.replace("ssl_server_cert_file = /scratch/cert.pem",
                                    "ssl_cert = </scratch/cert.pem")
            config = config.replace("ssl_server_key_file = /scratch/key.pem",
                                    "ssl_key = </scratch/key.pem")
            config = config.replace("mail_driver = maildir\nmail_home = ~\nmail_path = ~",
                                    "mail_location = maildir:/home/alice/mail/%d/%n")
        cls.dovecot_config.write_text(config)
        result = run(["doveconf", "-n"])
        if result.returncode:
            raise RuntimeError(result.stderr)
        cls.config_dump = result.stdout
        result = run(["doveconf", "-h", "mail_plugin_dir"])
        if not (Path(result.stdout.strip()) / "auth/libauthdb_lua.so").is_file():
            raise RuntimeError("Native Lua authentication plugin not found")
        cls.daemon_output = open("/scratch/daemon-output", "w+")
        cls.daemon = subprocess.Popen(["dovecot", "-F"], stdout=cls.daemon_output,
                                      stderr=subprocess.STDOUT)
        cls.addClassCleanup(cls.stop_daemon)
        cls.wait_socket()
        cls.context = ssl.create_default_context(cafile="/scratch/cert.pem")
        cls.context.check_hostname = False  # Synthetic certificate / isolated loopback.
        cls.exim_spool = SCRATCH / "exim"
        cls.exim_spool.mkdir()
        os.chown(cls.exim_spool, 12001, 8)
        Path("/scratch/exim-native.conf").write_text("""
primary_hostname = password-test.invalid
spool_directory = /scratch/exim
log_file_path = /scratch/exim/%slog
exim_user = 12001
exim_group = 8
daemon_smtp_ports = 1587
local_interfaces = 127.0.0.1
tls_advertise_hosts = *
tls_certificate = /scratch/cert.pem
tls_privatekey = /scratch/key.pem
begin authenticators
plain:
  driver = dovecot
  public_name = PLAIN
  server_socket = /run/dovecot/auth-client
  server_set_id = $auth1
login:
  driver = dovecot
  public_name = LOGIN
  server_socket = /run/dovecot/auth-client
  server_set_id = $auth1
""")
        cls.exim = subprocess.Popen(
            ["exim4", "-bdf", "-C", "/scratch/exim-native.conf"],
            stdout=cls.daemon_output, stderr=subprocess.STDOUT)
        cls.addClassCleanup(cls.stop_exim)
        for attempt in range(100):
            try:
                with socket.create_connection(("127.0.0.1", 1587), timeout=1):
                    break
            except OSError:
                if cls.exim.poll() is not None:
                    raise RuntimeError("Fixture Exim exited: " + cls.diagnostics())
                time.sleep(0.05)
        else:
            raise RuntimeError("Fixture Exim listener unavailable")

    @classmethod
    def diagnostics(cls):
        cls.daemon_output.flush()
        cls.daemon_output.seek(0)
        result = cls.daemon_output.read()
        log = SCRATCH / "dovecot.log"
        if log.exists():
            result += log.read_text()
        return cls.config_dump + result[-12000:]

    @classmethod
    def wait_socket(cls):
        for attempt in range(100):
            if Path("/run/dovecot/auth-client").exists():
                return
            if cls.daemon.poll() is not None:
                raise RuntimeError("Fixture Dovecot exited: " + cls.diagnostics())
            time.sleep(0.05)
        raise RuntimeError("Fixture Dovecot socket unavailable: " + cls.diagnostics())

    @classmethod
    def stop_daemon(cls):
        if cls.daemon.poll() is None:
            cls.daemon.terminate()
            cls.daemon.wait(timeout=10)
        cls.daemon_output.close()

    @classmethod
    def stop_exim(cls):
        if cls.exim.poll() is None:
            cls.exim.terminate()
            cls.exim.wait(timeout=10)

    @staticmethod
    def policy(value):
        (HESTIA / "conf/hestia.conf").write_text(
            "IMAP_SYSTEM='dovecot'\nMAIL_SYSTEM='exim4'\nROOT_USER='alice'\n"
            f"POLICY_MAIL_UPGRADE_MD5='{value}'\n")

    def setUp(self):
        self.policy("yes")
        (HESTIA / "data/users/alice/user.conf").write_text("USER='alice'\nSUSPENDED='no'\n")
        for name in ["web", "dns", "db", "cron", "backup"]:
            (HESTIA / f"data/users/alice/{name}.conf").write_text("")
        (HESTIA / "data/ips").mkdir(exist_ok=True)
        (HESTIA / "data/ips/127.0.0.1").write_text("OWNER='alice'\nSTATUS='shared'\n")
        type(self).serial += 1
        self.domain = f"fixture-{self.serial}.invalid"
        self.email = f"bob@{self.domain}"
        self.maildir = Path(f"/home/alice/mail/{self.domain}/bob")
        for directory in ["cur", "new", "tmp"]:
            path = self.maildir / directory
            path.mkdir(parents=True)
        for path in [self.maildir, *self.maildir.iterdir()]:
            os.chown(path, 12000, 8)
        self.runtime_dir = Path(f"/home/alice/conf/mail/{self.domain}")
        self.runtime_dir.mkdir(parents=True)
        self.runtime = self.runtime_dir / "passwd"
        self.metadata = HESTIA / f"data/users/alice/mail/{self.domain}.conf"
        self.metadata.write_text(f"ACCOUNT='bob' MD5='{self.legacy}' QUOTA='0' SUSPENDED='no'\n")
        self.runtime.write_text(f"bob:{self.legacy}:alice:mail::{self.maildir}:0:\n")
        os.chown(self.runtime, 107, 8)
        self.runtime.chmod(0o640)
        Path(f"/etc/exim4/domains/{self.domain}").symlink_to(self.runtime_dir)
        with (HESTIA / "data/users/alice/mail.conf").open("a") as file:
            file.write(f"DOMAIN='{self.domain}' SUSPENDED='no' DKIM_SELECTOR='mail' "
                       "WEBMAIL='none' SSL='no' RATE_LIMIT='20'\n")
        self.before = (self.metadata.read_bytes(), self.runtime.read_bytes())

    def authenticate(self, password=PASSWORD, service="imap"):
        with socket.socket(socket.AF_UNIX, socket.SOCK_STREAM) as client:
            client.settimeout(20)
            client.connect("/run/dovecot/auth-client")
            stream = client.makefile("rwb", buffering=0)
            stream.write(f"VERSION\t1\t2\nCPID\t{os.getpid()}\n".encode())
            while True:
                line = stream.readline()
                if not line:
                    self.fail("Dovecot closed handshake: " + self.diagnostics())
                if line.startswith(b"DONE"):
                    break
            response = base64.b64encode(b"\0" + self.email.encode() + b"\0" + password.encode())
            stream.write(b"AUTH\t1\tPLAIN\tservice=" + service.encode()
                         + b"\tsecured\tresp=" + response + b"\n")
            while True:
                line = stream.readline()
                if not line:
                    self.fail("Dovecot closed authentication: " + self.diagnostics())
                if line.startswith((b"OK\t1", b"FAIL\t1")):
                    return line.startswith(b"OK\t1")

    def assert_migrated(self):
        runtime_hash = self.runtime.read_text().split(":")[1]
        cached_hash = re.search(r"MD5='([^']+)'", self.metadata.read_text())[1]
        self.assertTrue(runtime_hash.startswith(self.expected_scheme), self.diagnostics())
        self.assertEqual(cached_hash, runtime_hash)
        self.assertEqual(self.runtime.stat().st_mode & 0o777, 0o640)
        self.assertEqual(self.runtime.stat().st_uid, 107)
        return runtime_hash

    def assert_unchanged(self):
        self.assertEqual((self.metadata.read_bytes(), self.runtime.read_bytes()), self.before)

    def test_configuration_command_enable_disable(self):
        command = str(HESTIA / "bin/v-change-sys-mail-password-upgrade")
        hook = Path("/etc/dovecot/conf.d/auth-hestia-password-upgrade.conf.ext")
        try:
            result = run([command, "no"])
            self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
            self.assertFalse(hook.exists())
            self.assertEqual(self.authenticate(), not self.dovecot24)
            self.assert_unchanged()
        finally:
            result = run([command, "yes"])
            self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        self.assertTrue(hook.exists())
        for attempt in range(3):
            if self.authenticate():
                break
        else:
            self.fail("Migration unavailable after enabling: " + self.diagnostics())
        self.assert_migrated()

    def test_wrong_password_denied_without_writes(self):
        self.assertFalse(self.authenticate("Incorrect-Fixture-123!"))
        self.assert_unchanged()

    def test_native_migration_and_repeat(self):
        self.assertTrue(self.authenticate(), self.diagnostics())
        migrated = self.assert_migrated()
        self.assertTrue(self.authenticate(), self.diagnostics())
        self.assertEqual(self.assert_migrated(), migrated)

    def test_warmed_passwd_file_cache(self):
        # passwd-file skips stat() after any lookup in the same wall-clock
        # second, even with auth_cache_size=0. Warm a DIFFERENT account first.
        result = run([PHP, "-r", "echo '{BLF-CRYPT}'.password_hash("
                      "stream_get_contents(STDIN), PASSWORD_BCRYPT, ['cost'=>4]);"],
                     input=PASSWORD)
        self.assertEqual(result.returncode, 0, result.stderr)
        with self.runtime.open("a") as file:
            file.write(f"warm:{result.stdout}:alice:mail::{self.maildir}:0:\n")
        with self.metadata.open("a") as file:
            file.write(f"ACCOUNT='warm' MD5='{result.stdout}' QUOTA='0' SUSPENDED='no'\n")
        old = time.time() - 5
        os.utime(self.runtime, (old, old))
        # Fixture-only alignment makes the cold MD5->bcrypt preflight complete
        # inside the already-cached second unless the production guard waits.
        time.sleep(1.05 - time.time() % 1)
        original = self.email
        self.email = f"warm@{self.domain}"
        try:
            self.assertTrue(self.authenticate(), self.diagnostics())
        finally:
            self.email = original
        self.assertTrue(self.authenticate(), self.diagnostics())
        self.assert_migrated()

    def test_disabled_policy_preserves_native_legacy_behavior(self):
        self.policy("no")
        self.assertEqual(self.authenticate(), not self.dovecot24)
        self.assert_unchanged()

    def test_disabled_policy_allows_modern_hash(self):
        self.assertTrue(self.authenticate(), self.diagnostics())
        migrated = self.assert_migrated()
        self.policy("no")
        self.assertTrue(self.authenticate(), self.diagnostics())
        self.assertEqual(self.assert_migrated(), migrated)

    def test_suspended_mailbox(self):
        self.metadata.write_text(self.metadata.read_text().replace("SUSPENDED='no'", "SUSPENDED='yes'"))
        before = (self.metadata.read_bytes(), self.runtime.read_bytes())
        self.assertFalse(self.authenticate())
        self.assertEqual((self.metadata.read_bytes(), self.runtime.read_bytes()), before)

    def test_suspended_user(self):
        (HESTIA / "data/users/alice/user.conf").write_text("USER='alice'\nSUSPENDED='yes'\n")
        self.assertFalse(self.authenticate())
        self.assert_unchanged()

    def test_suspended_domain(self):
        domains = HESTIA / "data/users/alice/mail.conf"
        domains.write_text(domains.read_text().replace(
            f"DOMAIN='{self.domain}' SUSPENDED='no'", f"DOMAIN='{self.domain}' SUSPENDED='yes'"))
        self.assertFalse(self.authenticate())
        self.assert_unchanged()

    def test_tls_imap_migration(self):
        with imaplib.IMAP4_SSL("127.0.0.1", 1993, ssl_context=self.context, timeout=20) as client:
            status, _ = client.login(self.email, PASSWORD)
            self.assertEqual(status, "OK")
        self.assert_migrated()

    def test_tls_pop3_migration(self):
        client = poplib.POP3_SSL("127.0.0.1", 1995, context=self.context, timeout=20)
        try:
            client.user(self.email)
            self.assertTrue(client.pass_(PASSWORD).startswith(b"+OK"))
            client.quit()
        finally:
            client.close()
        self.assert_migrated()

    def test_starttls_smtp_auth_migration(self):
        with smtplib.SMTP("127.0.0.1", 1587, timeout=20) as client:
            client.starttls(context=self.context)
            client.ehlo()
            code, _ = client.auth("PLAIN", lambda: "\0" + self.email + "\0" + PASSWORD)
            self.assertEqual(code, 235)
        self.assert_migrated()

    def test_metadata_roundtrip_after_reload(self):
        self.assertTrue(self.authenticate(), self.diagnostics())
        migrated = self.assert_migrated()
        result = run(["dovecot", "reload"])
        self.assertEqual(result.returncode, 0, result.stderr)
        cached = re.search(r"MD5='([^']+)'", self.metadata.read_text())[1]
        self.runtime.write_text(f"bob:{cached}:alice:mail::{self.maildir}:0:\n")
        self.assertTrue(self.authenticate(), self.diagnostics())
        self.assertEqual(self.assert_migrated(), migrated)

    def test_real_mail_domain_rebuild(self):
        self.assertTrue(self.authenticate(), self.diagnostics())
        migrated = self.assert_migrated()
        result = run([str(HESTIA / "bin/v-rebuild-mail-domain"), "alice", self.domain, "no"])
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        self.assertEqual(result.stderr, "")
        self.assertEqual(self.runtime.read_text().split(":")[1], migrated)
        self.assertIn("MD5='" + migrated + "'", self.metadata.read_text())
        self.assertTrue(self.authenticate(), self.diagnostics())

    def administrative_race(self, command, *args):
        ready = SCRATCH / "migration-ready"
        release = SCRATCH / "migration-release"
        ready.unlink(missing_ok=True)
        release.unlink(missing_ok=True)
        attempted = SCRATCH / "admin-gate-attempt"
        observer = SCRATCH / "observe-admin-gate"
        attempted.unlink(missing_ok=True)
        writer = None
        process = subprocess.Popen(
            [PHP, "-d", "auto_prepend_file=/work/test/mail-password-upgrade-barrier.php",
             str(HESTIA / "func/dovecot-password-upgrade.php")],
            stdin=subprocess.PIPE, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
        try:
            process.stdin.write(self.email + "\n" + PASSWORD + "\n")
            process.stdin.close()
            for attempt in range(500):
                if ready.exists():
                    break
                if process.poll() is not None:
                    self.fail("Migration exited before test barrier")
                time.sleep(0.01)
            else:
                self.fail("Migration never reached test barrier")
            observer.touch()
            writer_args = [str(HESTIA / "bin" / command), "alice", self.domain]
            if command != "v-rebuild-mail-domain":
                writer_args.append("bob")
            writer = subprocess.Popen([*writer_args, *args], text=True,
                                      stdout=subprocess.PIPE, stderr=subprocess.PIPE)
            for attempt in range(500):
                if attempted.exists():
                    break
                if writer.poll() is not None:
                    self.fail("Administrative writer exited before the shared-gate wait")
                time.sleep(0.01)
            else:
                self.fail("Administrative writer never reached the shared-gate wait")
            # The migration is paused immediately before its first rename:
            # a writer must not pass the gate or modify either password file.
            self.assertIsNone(writer.poll())
            self.assert_unchanged()
            with (HESTIA / "data/.mail-password-upgrade.lock").open("a") as probe:
                with self.assertRaises(BlockingIOError):
                    fcntl.flock(probe, fcntl.LOCK_SH | fcntl.LOCK_NB)
        finally:
            observer.unlink(missing_ok=True)
            release.touch()
            process.wait(timeout=15)
            if writer is not None:
                stdout, stderr = writer.communicate(timeout=20)
                self.assertEqual(writer.returncode, 0, stdout + stderr)
                self.assertEqual(stderr, "")
        stderr = process.stderr.read()
        process.stdout.close()
        process.stderr.close()
        self.assertEqual(process.returncode, 0, stderr)
        return self.metadata.read_text(), self.runtime.read_text()

    def test_race_password_reset_survives_migration(self):
        replacement = "Replacement-Fixture-123!"
        self.administrative_race("v-change-mail-account-password", replacement)
        self.assertTrue(self.authenticate(replacement), self.diagnostics())
        self.assertFalse(self.authenticate())

    def test_race_suspension_survives_migration(self):
        metadata, runtime = self.administrative_race("v-suspend-mail-account")
        self.assertIn("SUSPENDED='yes'", metadata)
        self.assertEqual(runtime.split(":")[1], "SUSPENDED")
        self.assertFalse(self.authenticate())

    def test_race_quota_change_survives_migration(self):
        metadata, runtime = self.administrative_race("v-change-mail-account-quota", "50")
        self.assertIn("QUOTA='50'", metadata)
        self.assertEqual(runtime.split(":")[6], "50")
        self.assertTrue(self.authenticate(), self.diagnostics())

    def test_race_rebuild_uses_migrated_hash(self):
        metadata, runtime = self.administrative_race("v-rebuild-mail-domain", "no")
        migrated = runtime.split(":")[1]
        self.assertTrue(migrated.startswith(self.expected_scheme))
        self.assertIn("MD5='" + migrated + "'", metadata)
        self.assertTrue(self.authenticate(), self.diagnostics())

    def test_busy_administrative_gate_fails_without_writes(self):
        with (HESTIA / "data/.mail-password-upgrade.lock").open("a") as gate:
            fcntl.flock(gate, fcntl.LOCK_SH)
            result = run([PHP, str(HESTIA / "func/dovecot-password-upgrade.php")],
                         input=self.email + "\n" + PASSWORD + "\n")
        self.assertEqual(result.returncode, 111, result.stderr)
        self.assert_unchanged()
        self.assertEqual(list(self.runtime_dir.glob(".password-upgrade-*")), [])
        self.assertEqual(list(self.metadata.parent.glob(".password-upgrade-*")), [])

    def test_nested_rebuild_writer_does_not_deadlock(self):
        self.assertTrue(self.authenticate(), self.diagnostics())
        migrated = self.assert_migrated()
        domains = HESTIA / "data/users/alice/mail.conf"
        domains.write_text(next(line for line in domains.read_text().splitlines()
                               if line.startswith(f"DOMAIN='{self.domain}'")) + "\n")
        result = run([str(HESTIA / "bin/v-rebuild-mail-domains"), "alice"])
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        self.assertEqual(result.stderr, "")
        self.assertEqual(self.runtime.read_text().split(":")[1], migrated)
        self.assertTrue(self.authenticate(), self.diagnostics())

    def test_interrupted_metadata_commit_recovery(self):
        self.assertTrue(self.authenticate(), self.diagnostics())
        migrated = self.assert_migrated()
        self.runtime.write_bytes(self.before[1])
        self.assertTrue(self.authenticate(), self.diagnostics())
        self.assertEqual(self.assert_migrated(), migrated)

    def test_long_password_scheme_limits(self):
        password = "x" * 100
        result = run([PHP, "-r", "echo crypt(stream_get_contents(STDIN), '$1$fixture$');"],
                     input=password)
        self.assertEqual(result.returncode, 0, result.stderr)
        old = "{MD5}" + result.stdout
        self.runtime.write_text(self.runtime.read_text().replace(self.legacy, old))
        self.metadata.write_text(self.metadata.read_text().replace(self.legacy, old))
        self.before = (self.metadata.read_bytes(), self.runtime.read_bytes())
        if self.expected_scheme == "{BLF-CRYPT}":
            self.assertFalse(self.authenticate(password))
            self.assert_unchanged()
        else:
            self.assertTrue(self.authenticate(password), self.diagnostics())
            migrated = self.assert_migrated()
            self.assertFalse(self.authenticate(password[:72] + "y" * 28))
            self.assertTrue(self.authenticate(password), self.diagnostics())
            self.assertEqual(self.assert_migrated(), migrated)

    def test_zz_no_plaintext_in_logs(self):
        self.assertTrue(self.authenticate(), self.diagnostics())
        for path in [SCRATCH / "dovecot.log", SCRATCH / "daemon-output",
                     self.exim_spool / "mainlog", self.exim_spool / "rejectlog"]:
            if path.exists():
                with self.subTest(log=path.name):
                    logs = path.read_text()
                    self.assertNotIn(PASSWORD, logs)
                    self.assertNotIn("Incorrect-Fixture-123!", logs)


if __name__ == "__main__":
    unittest.main(verbosity=2)
