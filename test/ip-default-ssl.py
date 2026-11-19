#!/usr/bin/env python3
"""Isolated per-IP HTTPS selection tests; no system files/services are changed.

Run: python3 test/ip-default-ssl.py
Uses real Hestia validation and OpenSSL, with an nginx test/reload shim.
"""
from pathlib import Path
import os
import shutil
import subprocess
import tempfile
import unittest

ROOT = Path(__file__).resolve().parents[1]
IP = "198.18.0.125"
DOMAIN = "staging.example.invalid"
COMMAND = "apply-ip-https.sh"


class DefaultCertificate(unittest.TestCase):
    def setUp(self):
        (ROOT / "tmp").mkdir(exist_ok=True)
        self.temporary = tempfile.TemporaryDirectory(dir=ROOT / "tmp")
        self.addCleanup(self.temporary.cleanup)
        self.home = Path(self.temporary.name)
        for name in ("bin", "func", "conf", "data/queue", "data/ips", "log", "apache",
                     "nginx", "shim", "data/users/alice/ssl", "data/users/bob/ssl"):
            (self.home / name).mkdir(parents=True)
        (self.home / "bootstrap").write_text(f"HESTIA='{self.home}'\n")
        self.settings = self.home / "conf/hestia.conf"
        self.settings.write_text("ROOT_USER='admin'\nWEB_SYSTEM='apache2'\n"
                                 "PROXY_SYSTEM='nginx'\nWEB_SSL='mod_ssl'\nWEB_SSL_PORT='8443'\n")
        for name in ("main.sh", "ip.sh", "domain.sh", "ip-default-ssl.sh", "object-parser.php"):
            text = (ROOT / "func" / name).read_text()
            text = text.replace('HESTIA_PHP="$HESTIA/php/bin/php"',
                                f'HESTIA_PHP="{shutil.which("php")}"')
            text = text.replace("HOMEDIR='/home'", f"HOMEDIR='{self.home}/home'")
            (self.home / "func" / name).write_text(text)
        text = (ROOT / "func" / COMMAND).read_text()
        text = text.replace("/etc/hestiacp/hestia.conf", str(self.home / "bootstrap"))
        text = text.replace("/etc/nginx/conf.d", str(self.home / "nginx"))
        text = text.replace("/etc/$WEB_SYSTEM/conf.d", str(self.home / "apache"))
        self.executable(self.home / "func" / COMMAND, text)
        self.executable(self.home / "bin/v-log-action", "#!/bin/bash\nexit 0\n")
        self.record = self.home / "data/ips" / IP
        self.record.write_text("OWNER='alice'\nSTATUS='dedicated'\nNAT=''\n")
        self.conf = self.home / "nginx" / (IP + ".conf")
        self.original = f"""server {{
    listen {IP}:80 default_server;
    server_name _;
}}
server {{
    listen {IP}:443 default_server ssl;
    server_name _;
    ssl_certificate     /original/certificate.crt;
    ssl_certificate_key /original/certificate.key;
    return 301 http://$host$request_uri;
}}
"""
        self.conf.write_text(self.original)
        self.other = self.home / "nginx/198.18.0.126.conf"
        self.other.write_text(self.original.replace(IP, "198.18.0.126"))
        self.backend = self.home / "apache" / (IP + ".conf")
        self.original_backend = f"""<VirtualHost {IP}:8080>
    ServerName {IP}
</VirtualHost>
<VirtualHost {IP}:8443>
    ServerName     {IP}
</VirtualHost>
"""
        self.backend.write_text(self.original_backend)
        for user in ("alice", "bob"):
            (self.home / f"data/users/{user}/user.conf").write_text("SUSPENDED='no'\n")
            self.site(user)
            self.cert(user)
            directory = self.home / f"home/{user}/conf/web/{DOMAIN}"
            directory.mkdir(parents=True)
            (directory / "nginx.ssl.conf").write_text(
                "server {\nlocation / {\nproxy_ssl_name $host;\n}\n}\n")
        self.executable(self.home / "shim/nginx", """#!/usr/bin/python3
from pathlib import Path
import os,sys
base=Path(os.environ["TEST_HOME"])
with (base/"nginx-calls").open("a") as f: f.write(" ".join(sys.argv[1:])+"\\n")
if (base/"fail-once").exists():
    (base/"fail-once").unlink()
    sys.exit(1)
if sys.argv[1:]==["-s","reload"] and (base/"fail-reload-once").exists():
    (base/"fail-reload-once").unlink()
    sys.exit(1)
if sys.argv[1:]==["-s","reload"] and (base/"fail-reloads").exists():
    sys.exit(1)
""")
        self.executable(self.home / "shim/apachectl", """#!/usr/bin/python3
from pathlib import Path
import os,sys
base=Path(os.environ["TEST_HOME"])
if (base/"fail-apache-once").exists():
    (base/"fail-apache-once").unlink()
    sys.exit(1)
""")
        self.executable(self.home / "bin/v-restart-web", "#!/bin/bash\nexit 0\n")
        self.env = {**os.environ, "PATH": str(self.home / "shim") + ":" + os.environ["PATH"],
                    "TEST_HOME": str(self.home)}

    def executable(self, path, text):
        path.write_text(text)
        path.chmod(0o700)

    def command(self, name):
        text = (ROOT / "bin" / name).read_text()
        text = text.replace("/etc/hestiacp/hestia.conf", str(self.home / "bootstrap"))
        path = self.home / "bin" / name
        self.executable(path, text)
        return path

    def site(self, user="alice", ip=IP, aliases=IP, ssl="yes", suspended="no"):
        (self.home / f"data/users/{user}/web.conf").write_text(
            f"DOMAIN='{DOMAIN}' IP='{ip}' ALIAS='{aliases}' SSL='{ssl}' PROXY='default' SUSPENDED='{suspended}'\n")

    def cert(self, user="alice", ip=IP):
        directory = self.home / f"data/users/{user}/ssl"
        args = ["openssl", "req", "-x509", "-newkey", "ec",
                "-pkeyopt", "ec_paramgen_curve:prime256v1", "-nodes", "-days", "6",
                "-subj", "/CN=" + DOMAIN, "-addext", f"subjectAltName=DNS:{DOMAIN},IP:{ip}",
                "-keyout", str(directory / (DOMAIN + ".key")),
                "-out", str(directory / (DOMAIN + ".crt"))]
        subprocess.run(args, capture_output=True, check=True, timeout=10)
        shutil.copyfile(directory / (DOMAIN + ".crt"), directory / (DOMAIN + ".pem"))

    def select(self, user="alice", domain=DOMAIN, ip=IP, success=True):
        result = subprocess.run([str(self.home / "func" / COMMAND), ip, user, domain],
                                env=self.env, capture_output=True, text=True, timeout=15)
        if success:
            self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        else:
            self.assertNotEqual(result.returncode, 0, result.stdout + result.stderr)
        return result

    def assert_untouched(self):
        self.assertEqual(self.conf.read_text(), self.original)
        self.assertFalse((self.home / "nginx-calls").exists())
        self.assertEqual(self.record.read_text(), "OWNER='alice'\nSTATUS='dedicated'\nNAT=''\n")

    def test_select_and_reset_preserves_other_ips(self):
        other = self.other.read_bytes()
        self.select()
        self.assertIn(f"/data/users/alice/ssl/{DOMAIN}.pem;", self.conf.read_text())
        self.assertIn("DEFAULT_SSL_USER='alice'", self.record.read_text())
        self.assertIn("return 301", self.conf.read_text())
        self.assertIn("ServerName ip-default-", self.backend.read_text())
        self.assertIn(f"proxy_ssl_name {DOMAIN};",
                      (self.home / f"home/alice/conf/web/{DOMAIN}/nginx.ssl.conf").read_text())
        self.assertEqual(self.other.read_bytes(), other)
        self.select("none", "none")
        self.assertEqual(self.conf.read_text(), self.original)
        self.assertEqual(self.backend.read_text(), self.original_backend)
        self.assertIn("proxy_ssl_name $host;", (self.home / f"home/alice/conf/web/{DOMAIN}/nginx.ssl.conf").read_text())
        self.assertIn("DEFAULT_SSL_DOMAIN=''", self.record.read_text())
        self.assertFalse((self.home / f"data/ip-default-ssl/{IP}.conf").exists())
        self.select("none", "none")

    def sync(self, user="alice"):
        return self.helper(f"user={user}; USER_DATA=$HESTIA/data/users/$user; domain='{DOMAIN}'\n"
                           "source \"$HESTIA/func/ip.sh\"\nsource \"$HESTIA/func/domain.sh\"\n"
                           "sync_dedicated_ip_https")

    def test_automatic_activation_in_either_order(self):
        self.cert(ip="198.18.0.126")
        self.assertEqual(self.sync().returncode, 0)
        self.assert_untouched()
        self.cert()
        self.site(aliases="www." + DOMAIN)
        self.assertEqual(self.sync().returncode, 0)
        self.assert_untouched()
        self.site()
        self.assertEqual(self.sync().returncode, 0)
        self.assertIn("DEFAULT_SSL_USER='alice'", self.record.read_text())
        calls = (self.home / "nginx-calls").read_bytes()
        self.assertEqual(self.sync().returncode, 0)
        self.assertEqual((self.home / "nginx-calls").read_bytes(), calls)

    def test_automatic_activation_skips_shared_and_other_owners(self):
        for status, owner in (("shared", "alice"), ("dedicated", "bob")):
            record = f"OWNER='{owner}'\nSTATUS='{status}'\nNAT=''\n"
            self.record.write_text(record)
            self.assertEqual(self.sync().returncode, 0)
            self.assertEqual(self.record.read_text(), record)
            self.assertEqual(self.conf.read_text(), self.original)
            self.assertFalse((self.home / "nginx-calls").exists())

    def test_dedicating_ip_with_existing_site_activates_it(self):
        self.record.write_text("OWNER='alice'\nSTATUS='shared'\nNAT=''\n"
                               "U_WEB_DOMAINS='1'\nU_SYS_USERS='alice'\n")
        result = subprocess.run([str(self.command("v-change-sys-ip-status")), IP, "dedicated"],
                                env=self.env, capture_output=True, text=True, timeout=15)
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        self.assertIn("DEFAULT_SSL_USER='alice'", self.record.read_text())

    def test_configuration_failure_rolls_back(self):
        (self.home / "fail-once").touch()
        self.select(success=False)
        self.assertEqual(self.conf.read_text(), self.original)
        self.assertNotIn("DEFAULT_SSL_DOMAIN", self.record.read_text())
        self.assertFalse((self.home / f"data/ip-default-ssl/{IP}.conf").exists())
        self.assertEqual(list((self.home / "tmp").iterdir()), [])

    def test_apache_failure_rolls_back_listener_and_site(self):
        site_conf = self.home / f"home/alice/conf/web/{DOMAIN}/nginx.ssl.conf"
        before = self.conf.read_bytes(), self.backend.read_bytes(), site_conf.read_bytes()
        (self.home / "fail-apache-once").touch()
        self.select(success=False)
        self.assertEqual((self.conf.read_bytes(), self.backend.read_bytes(), site_conf.read_bytes()), before)
        self.assertNotIn("DEFAULT_SSL_DOMAIN", self.record.read_text())
        self.assertEqual(list((self.home / "data/ip-default-ssl").iterdir()), [])

    def test_backend_sni_change_is_scoped_to_ip_aliases(self):
        site_conf = self.home / f"home/alice/conf/web/{DOMAIN}/nginx.ssl.conf"
        original = site_conf.read_text() + "proxy_set_header Host $host;\n"
        site_conf.write_text(original)
        result = self.helper(f"prepare_ip_alias_backend_sni '{site_conf}' '{DOMAIN}' www.{DOMAIN}")
        self.assertEqual(result.returncode, 0)
        self.assertEqual(site_conf.read_text(), original)
        result = self.helper(f"prepare_ip_alias_backend_sni '{site_conf}' '{DOMAIN}' '{IP}'")
        self.assertEqual(result.returncode, 0)
        self.assertIn(f"proxy_ssl_name {DOMAIN};", site_conf.read_text())
        self.assertIn("proxy_set_header Host $host;", site_conf.read_text())

    def test_reload_failure_restores_previous_selection(self):
        self.select()
        before = self.conf.read_bytes(), self.record.read_bytes()
        (self.home / "fail-reload-once").touch()
        self.select("none", "none", success=False)
        self.assertEqual((self.conf.read_bytes(), self.record.read_bytes()), before)
        self.select("none", "none")
        self.assertEqual(self.conf.read_text(), self.original)

    def test_missing_baseline_fails_without_changes(self):
        self.select()
        before = self.conf.read_bytes(), self.record.read_bytes()
        (self.home / f"data/ip-default-ssl/{IP}.conf").unlink()
        self.select("none", "none", success=False)
        self.assertEqual((self.conf.read_bytes(), self.record.read_bytes()), before)

    def test_status_change_releases_selection(self):
        self.record.write_text("OWNER='admin'\nSTATUS='dedicated'\nNAT=''\n"
                               "U_WEB_DOMAINS='1'\nU_SYS_USERS='admin'\n")
        shutil.copytree(self.home / "data/users/alice", self.home / "data/users/admin")
        shutil.copytree(self.home / "home/alice", self.home / "home/admin")
        self.select("admin")
        result = subprocess.run([str(self.command("v-change-sys-ip-status")), IP, "shared"],
                                env=self.env, capture_output=True, text=True, timeout=15)
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        self.assertEqual(self.conf.read_text(), self.original)
        self.assertIn("STATUS='shared'", self.record.read_text())
        self.assertIn("DEFAULT_SSL_DOMAIN=''", self.record.read_text())

    def test_system_ip_rename_aborts_when_release_fails(self):
        self.select()
        (self.home / "func/domain.sh").write_text("")
        self.executable(self.home / "shim/ip", """#!/usr/bin/python3
import json,sys
if 'link' in sys.argv:
    print(json.dumps([{'ifname':'eth0','link_type':'ether','linkinfo':{'info_kind':None}}]))
else:
    print(json.dumps([{'addr_info':[{'scope':'global','local':'198.18.0.126'}]}]))
""")
        (self.home / "fail-once").touch()
        before = self.conf.read_bytes(), self.record.read_bytes()
        result = subprocess.run([str(self.command("v-update-sys-ip"))], env=self.env,
                                capture_output=True, text=True, timeout=15)
        self.assertNotEqual(result.returncode, 0, result.stdout + result.stderr)
        self.assertEqual((self.conf.read_bytes(), self.record.read_bytes()), before)
        self.assertFalse((self.home / "data/ips/198.18.0.126").exists())

    def test_mail_certificate_collision_aborts_before_replacing_shared_files(self):
        collision = "mail." + DOMAIN
        directory = self.home / "data/users/alice"
        with (directory / "web.conf").open("a") as stream:
            stream.write(f"DOMAIN='{collision}' IP='{IP}' ALIAS='{IP}' SSL='yes' SUSPENDED='no'\n")
        for extension in ("crt", "pem", "key"):
            shutil.copyfile(directory / f"ssl/{DOMAIN}.{extension}",
                            directory / f"ssl/{collision}.{extension}")
        site = self.home / f"home/alice/conf/web/{collision}"
        site.mkdir()
        (site / "nginx.ssl.conf").write_text("proxy_ssl_name $host;\n")
        self.select(domain=collision)
        (directory / "mail.conf").write_text(f"DOMAIN='{DOMAIN}' SSL='yes' SUSPENDED='no'\n")
        with self.settings.open("a") as stream:
            stream.write("MAIL_SYSTEM='exim4'\n")
        (self.home / "func/domain.sh").write_text("is_web_domain_cert_valid() { :; }\n")
        files = [directory / f"ssl/{collision}.{ext}" for ext in ("crt", "pem", "key")]
        before = [path.read_bytes() for path in files]
        (self.home / "fail-once").touch()
        result = subprocess.run([str(self.command("v-update-mail-domain-ssl")), "alice",
                                 DOMAIN, str(directory / "ssl"), "no"], env=self.env,
                                capture_output=True, text=True, timeout=15)
        self.assertNotEqual(result.returncode, 0, result.stdout + result.stderr)
        self.assertEqual([path.read_bytes() for path in files], before)
        self.assertIn(f"DEFAULT_SSL_DOMAIN='{collision}'", self.record.read_text())

    def test_failed_rollback_preserves_recovery_and_blocks_mutations(self):
        (self.home / "fail-reloads").touch()
        result = self.select(success=False)
        self.assertIn("Manual recovery required", result.stderr)
        recovery = self.home / f"data/ip-default-ssl/{IP}.recovery"
        self.assertTrue(recovery.exists())
        transactions = list((self.home / "tmp").iterdir())
        self.assertEqual(len(transactions), 1)
        self.assertEqual((transactions[0] / "nginx.conf").read_text(), self.original)
        self.assertEqual((transactions[0] / "ip").read_text(),
                         "OWNER='alice'\nSTATUS='dedicated'\nNAT=''\n")
        self.assertTrue((self.home / f"data/ip-default-ssl/{IP}.conf").exists())
        (self.home / "fail-reloads").unlink()
        calls = (self.home / "nginx-calls").read_bytes()
        result = self.select("none", "none", success=False)
        self.assertIn("Manual recovery required", result.stdout + result.stderr)
        self.assertEqual((self.home / "nginx-calls").read_bytes(), calls)
        result = subprocess.run([str(self.command("v-delete-web-domain")), "alice", DOMAIN],
                                env=self.env, capture_output=True, text=True, timeout=15)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("Manual recovery required", result.stderr)
        self.assertTrue((self.home / f"data/users/alice/ssl/{DOMAIN}.key").exists())

    def test_failed_file_restoration_keeps_recovery_material(self):
        real_mv = shutil.which("mv")
        self.executable(self.home / "shim/mv", f"""#!/usr/bin/python3
from pathlib import Path
import os,sys
base=Path(os.environ['TEST_HOME'])
if sys.argv[-1]==str(base/'nginx/{IP}.conf'):
    if '/original/certificate.crt' in Path(sys.argv[-2]).read_text():
        sys.exit(1)
os.execv({real_mv!r}, [{real_mv!r}]+sys.argv[1:])
""")
        (self.home / "fail-once").touch()
        result = self.select(success=False)
        self.assertIn("Manual recovery required", result.stderr)
        self.assertTrue((self.home / f"data/ip-default-ssl/{IP}.recovery").exists())
        transaction, = list((self.home / "tmp").iterdir())
        self.assertEqual((transaction / "nginx.conf").read_text(), self.original)
        self.assertIn("/alice/ssl/", self.conf.read_text())

    def test_site_on_other_ip_rejected(self):
        self.site(ip="198.18.0.126")
        self.select(success=False)
        self.assert_untouched()

    def test_missing_ip_alias_rejected(self):
        self.site(aliases="www." + DOMAIN)
        self.select(success=False)
        self.assert_untouched()

    def test_wrong_ip_san_rejected(self):
        self.cert(ip="198.18.0.126")
        self.select(success=False)
        self.assert_untouched()

    def test_served_pem_not_unrelated_crt_is_validated(self):
        self.cert("bob", ip="198.18.0.126")
        shutil.copyfile(self.home / f"data/users/bob/ssl/{DOMAIN}.pem",
                        self.home / f"data/users/alice/ssl/{DOMAIN}.pem")
        self.select(success=False)
        self.assert_untouched()

    def expire_certificate(self):
        directory = self.home / "data/users/alice/ssl"
        path = directory / (DOMAIN + ".crt")
        request = directory / "expired.csr"
        key = directory / (DOMAIN + ".key")
        subprocess.run(["openssl", "x509", "-x509toreq", "-in", str(path),
                        "-signkey", str(key), "-out", str(request)],
                       capture_output=True, check=True, timeout=10)
        (directory / "index").touch()
        (directory / "serial").write_text("01\n")
        config = directory / "ca.conf"
        config.write_text(f"""[ca]
default_ca=test
[test]
database={directory}/index
new_certs_dir={directory}
serial={directory}/serial
default_md=sha256
policy=policy
x509_extensions=extensions
[policy]
commonName=supplied
[extensions]
subjectAltName=DNS:{DOMAIN},IP:{IP}
""")
        expired = directory / "expired.crt"
        subprocess.run(["openssl", "ca", "-selfsign", "-batch", "-notext", "-config",
                        str(config), "-in", str(request), "-keyfile", str(key),
                        "-cert", str(path), "-out", str(expired),
                        "-startdate", "20000101000000Z", "-enddate", "20010101000000Z"],
                       capture_output=True, check=True, timeout=10)
        path.write_bytes(expired.read_bytes())
        (directory / (DOMAIN + ".pem")).write_bytes(expired.read_bytes())

    def test_expired_certificate_rejected(self):
        self.expire_certificate()
        self.select(success=False)
        self.assert_untouched()

    def test_expired_replacement_rejected(self):
        self.select()
        self.expire_certificate()
        result = self.helper(f"user=alice\ndomain='{DOMAIN}'\n"
                             f"ssl_dir='{self.home}/data/users/alice/ssl'\n"
                             "check_ip_default_ssl_certificate")
        self.assertNotEqual(result.returncode, 0)

    def test_missing_private_key_rejected(self):
        (self.home / f"data/users/alice/ssl/{DOMAIN}.key").unlink()
        self.select(success=False)
        self.assert_untouched()

    def test_atomic_updates_preserve_file_modes_and_ip_inventory(self):
        self.conf.chmod(0o640)
        self.record.chmod(0o660)
        self.select()
        self.assertEqual(self.conf.stat().st_mode & 0o777, 0o640)
        self.assertEqual(self.record.stat().st_mode & 0o777, 0o660)
        self.assertEqual([p.name for p in (self.home / "data/ips").iterdir()], [IP])

    def test_suspended_site_rejected(self):
        self.site(suspended="yes")
        self.select(success=False)
        self.assert_untouched()

    def test_suspended_user_rejected(self):
        (self.home / "data/users/alice/user.conf").write_text("SUSPENDED='yes'\n")
        self.select(success=False)
        self.assert_untouched()

    def test_non_ssl_site_rejected(self):
        self.site(ssl="no")
        self.select(success=False)
        self.assert_untouched()

    def test_dedicated_ip_other_owner_rejected(self):
        self.record.write_text("OWNER='bob'\nSTATUS='dedicated'\nNAT=''\n")
        self.select(success=False)
        self.assertEqual(self.conf.read_text(), self.original)

    def test_primary_name_must_match_dedicated_public_address(self):
        public = "198.18.0.127"
        self.record.write_text(f"OWNER='alice'\nSTATUS='dedicated'\nNAT='{public}'\n")
        for name, addr, owner, ok in ((public, IP, "alice", True), (IP, IP, "alice", False),
                                      (public, IP, "bob", False), (public, public, "alice", True)):
            with self.subTest(name=name, addr=addr, owner=owner):
                result = self.helper(f"check_primary_web_ip '{name}' '{addr}' '{owner}'")
                self.assertEqual(result.returncode == 0, ok, result.stdout + result.stderr)
        self.record.write_text(f"OWNER='alice'\nSTATUS='shared'\nNAT='{public}'\n")
        self.assertNotEqual(self.helper(f"check_primary_web_ip '{public}' '{IP}' alice").returncode, 0)

    def test_nat_ip_uses_public_alias_and_san(self):
        public = "198.18.0.127"
        self.record.write_text(f"OWNER='alice'\nSTATUS='dedicated'\nNAT='{public}'\n")
        self.site(ip=public, aliases=public)
        self.cert(ip=public)
        result = self.sync()
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        self.assertIn(f"DEFAULT_SSL_DOMAIN='{DOMAIN}'", self.record.read_text())
        self.select("none", "none")
        self.assertEqual(self.conf.read_text(), self.original)

    def test_apache_only_rejected(self):
        self.settings.write_text("ROOT_USER='admin'\nWEB_SYSTEM='apache2'\nPROXY_SYSTEM=''\n")
        self.select(success=False)
        self.assert_untouched()

    def test_nginx_standalone_supported(self):
        self.settings.write_text("ROOT_USER='admin'\nWEB_SYSTEM='nginx'\nPROXY_SYSTEM=''\n")
        self.select()

    def test_path_injection_rejected(self):
        for user, domain, ip in (("../alice", DOMAIN, IP), ("alice", "../../bad", IP),
                                 ("alice", DOMAIN, "../bad")):
            with self.subTest(user=user, domain=domain, ip=ip):
                self.select(user, domain, ip, success=False)
        self.assert_untouched()

    def test_unsupported_multiple_certificate_config_rejected(self):
        original = self.conf.read_text() + "ssl_certificate /second.crt;\n"
        self.conf.write_text(original)
        self.select(success=False)
        self.assertEqual(self.conf.read_text(), original)

    def test_reset_preserves_unrelated_config_edits(self):
        self.select()
        with self.conf.open("a") as output:
            output.write("# later unrelated edit\n")
        self.select("none", "none")
        self.assertEqual(self.conf.read_text(), self.original + "# later unrelated edit\n")

    def helper(self, script):
        return subprocess.run(["bash", "-c", f"""
source '{self.home}/bootstrap'
source "$HESTIA/func/main.sh"
source_conf "$HESTIA/conf/hestia.conf"
{script}
"""], env=self.env, capture_output=True, text=True, timeout=15)

    def test_lifecycle_release_is_selective(self):
        self.select()
        result = self.helper(f"release_ip_default_ssl bob '{DOMAIN}'\n"
                             f"release_ip_default_ssl alice '{DOMAIN}' www.{DOMAIN}")
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertIn("/alice/ssl/", self.conf.read_text())
        result = self.helper(f"release_ip_default_ssl alice '{DOMAIN}' '{IP}'")
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertEqual(self.conf.read_text(), self.original)

    def test_nested_lock_does_not_deadlock(self):
        self.select()
        result = self.helper(f"lock_ip_default_ssl\nrelease_ip_default_ssl alice '{DOMAIN}'")
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        self.assertEqual(self.conf.read_text(), self.original)

    def test_selection_waits_for_lifecycle_lock(self):
        holder = subprocess.Popen(["bash", "-c", f"""
source '{self.home}/bootstrap'
source "$HESTIA/func/main.sh"
lock_ip_default_ssl
echo locked
read -r release
"""], env=self.env, stdin=subprocess.PIPE, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
        self.addCleanup(lambda: holder.poll() is None and holder.kill())
        self.assertEqual(holder.stdout.readline().strip(), "locked")
        selection = subprocess.Popen([str(self.home / "func" / COMMAND), IP, "alice", DOMAIN],
                                     env=self.env, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
        self.addCleanup(lambda: selection.poll() is None and selection.kill())
        with self.assertRaises(subprocess.TimeoutExpired):
            selection.communicate(timeout=0.2)
        self.assertEqual(self.conf.read_text(), self.original)
        holder.communicate("release\n", timeout=5)
        output, errors = selection.communicate(timeout=15)
        self.assertEqual(selection.returncode, 0, output + errors)
        self.assertIn("/alice/ssl/", self.conf.read_text())

    def test_replacement_certificate_must_keep_ip_san(self):
        self.select()
        self.cert(ip="198.18.0.126")
        result = self.helper(f"user=alice\ndomain='{DOMAIN}'\n"
                             f"ssl_dir='{self.home}/data/users/alice/ssl'\n"
                             "check_ip_default_ssl_certificate")
        self.assertNotEqual(result.returncode, 0)

    def test_mutators_have_lifecycle_guards(self):
        guards = {
            "v-delete-web-domain": 'release_ip_default_ssl "$user" "$domain"',
            "v-delete-web-domain-ssl": 'release_ip_default_ssl "$user" "$domain"',
            "v-delete-web-domain-proxy": 'release_ip_default_ssl "$user" "$domain"',
            "v-suspend-web-domain": 'release_ip_default_ssl "$user" "$domain"',
            "v-change-web-domain-ip": 'release_ip_default_ssl "$user" "$domain"',
            "v-change-web-domain-name": 'release_ip_default_ssl "$user" "$domain"',
            "v-delete-web-domain-alias": 'release_ip_default_ssl "$user" "$domain" "$dom_alias"',
            "v-delete-user": 'release_ip_default_ssl "$user"',
            "v-suspend-user": 'release_ip_default_ssl "$user"',
            "v-change-sys-ip-owner": 'release_ip_default_ssl_address "$ip"',
            "v-change-sys-ip-nat": 'release_ip_default_ssl_address "$ip"',
            "v-change-sys-ip-status": 'release_ip_default_ssl_address "$ip"',
            "v-update-sys-ip": 'release_ip_default_ssl_address "$old_ip"',
            "v-add-mail-domain-ssl": 'release_ip_default_ssl "$user" "mail.$domain"',
            "v-update-mail-domain-ssl": 'release_ip_default_ssl "$user" "mail.$domain"',
            "v-delete-mail-domain-ssl": 'release_ip_default_ssl "$user" "mail.$domain"',
            "v-delete-mail-domain": 'release_ip_default_ssl "$user" "mail.$domain"',
            "v-change-mail-domain-sslcert": 'release_ip_default_ssl "$user" "mail.$domain"',
            "v-change-domain-owner": 'release_ip_default_ssl "$owner" "$domain"',
            "v-restore-user": 'release_ip_default_ssl "$user"',
            "v-restore-web-domain-restic": 'release_ip_default_ssl "$user" "$domain"',
            "v-restore-mail-domain-restic": 'release_ip_default_ssl "$user" "mail.$domain"',
            "v-delete-sys-ip": 'release_ip_default_ssl_address "$ip"',
        }
        for name, guard in guards.items():
            with self.subTest(command=name):
                source = (ROOT / "bin" / name).read_text()
                self.assertIn(guard, source)
                self.assertLess(source.index("check_hestia_demo_mode"), source.index(guard))
                self.assertIn(name, (ROOT / "func/ip-default-ssl.sh").read_text())
        for name in ("v-update-web-domain-ssl", "v-change-web-domain-sslcert"):
            self.assertIn("check_ip_default_ssl_certificate", (ROOT / "bin" / name).read_text())


if __name__ == "__main__":
    unittest.main(verbosity=2)
