#!/usr/bin/env python3
"""Isolated CSR/renewal regressions; no installed services or ACME requests.

Run: python3 test/letsencrypt-ip.py
Requires bash, OpenSSL, PHP and idn2. Fixtures stay below the project's tmp/.
"""
from pathlib import Path
import json
import os
import re
import subprocess
import tempfile
import unittest

ROOT = Path(__file__).resolve().parents[1]
DOMAIN = "staging.example.invalid"
IP = "198.18.0.125"


def run(*args, **kwargs):
    return subprocess.run(args, capture_output=True, text=True, timeout=30, **kwargs)


class Fixture(unittest.TestCase):
    def setUp(self):
        (ROOT / "tmp").mkdir(exist_ok=True)
        self.temporary = tempfile.TemporaryDirectory(dir=ROOT / "tmp")
        self.addCleanup(self.temporary.cleanup)
        self.home = Path(self.temporary.name)
        for directory in ("func", "bin", "conf", "data/ips", "data/users/alice/ssl", "runtime"):
            (self.home / directory).mkdir(parents=True)
        (self.home / "bootstrap").write_text(f"HESTIA='{self.home}'\n")
        (self.home / "conf/hestia.conf").write_text("")
        (self.home / "func/main.sh").write_text(f"""
BIN="$HESTIA/bin"
USER_DATA="$HESTIA/data/users/alice"
HESTIA_PHP=$(command -v php)
OK=0
E_INVALID=2
check_args() {{ [ "$2" -ge "$1" ] || exit 1; }}
check_result() {{ [ "$1" -eq 0 ] || {{ echo "$2"; exit "$1"; }}; }}
source_conf() {{ source "$1"; }}
is_format_valid() {{ :; }}
is_common_format_valid() {{ :; }}
is_common_format_spaces_valid() {{ :; }}
format_domain_idn() {{ domain_idn=$(idn2 --quiet "$domain"); }}
check_hestia_demo_mode() {{ :; }}
log_event() {{ :; }}
is_ip46_format_valid() {{
    "$HESTIA_PHP" -r 'exit(filter_var($argv[1], FILTER_VALIDATE_IP) ? 0 : 2);' "$1" || exit 2
}}
is_ip_format_valid() {{ is_ip46_format_valid "$1"; }}
search_objects() {{ [ "$1" != web ] || echo "${{TEST_DOMAIN:-{DOMAIN}}}"; }}
sanitize_config_file() {{ :; }}
get_object_value() {{
    case "$4" in
        '$SUSPENDED') echo no ;;
        '$LETSENCRYPT_FAIL_COUNT') echo 0 ;;
    esac
}}
parse_object_kv_list() {{
    ALIAS="$TEST_ALIASES"
    SSL_KEY_ALGO="$TEST_KEY_ALGO"
    IP="${{TEST_IP:-{IP}}}"
}}
source "$HESTIA/func/ip-default-ssl.sh"
""")
        (self.home / "func/ip-default-ssl.sh").write_text(
            (ROOT / "func/ip-default-ssl.sh").read_text())
        (self.home / "func/syshealth.sh").write_text("")
        self.executable("at", "#!/bin/bash\ncat >/dev/null\n")
        self.environment = {**os.environ, "TMPDIR": str(self.home / "runtime"),
                            "PATH": str(self.home / "bin") + ":" + os.environ["PATH"]}

    def executable(self, name, source):
        path = self.home / "bin" / name
        path.write_text(source)
        path.chmod(0o700)
        return path

    def command(self, name):
        source = (ROOT / "bin" / name).read_text()
        source = source.replace("/etc/hestiacp/hestia.conf", str(self.home / "bootstrap"))
        source = source.replace("/root/.rnd", str(self.home / "rnd"))
        return self.executable(name, source)


class CertificateRequest(Fixture):
    def generate(self, algorithm="ecdsa-256", aliases="", ips=IP, domain=DOMAIN):
        result = run(str(self.command("v-generate-ssl-cert")), domain, "", "US", "CA",
                     "Test City", "Test", "Test", aliases, "shell", algorithm, ips,
                     env=self.environment)
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        directory = Path(re.search(r"Directory: (.+)", result.stdout).group(1))
        # Production deliberately removes directory traversal permission; restore
        # it only in this test-owned fixture so non-root tests can inspect files.
        directory.chmod(0o700)
        csr = run("openssl", "req", "-in", str(directory / (domain + ".csr")),
                  "-noout", "-text")
        certificate = run("openssl", "x509", "-in", str(directory / (domain + ".crt")),
                          "-noout", "-text")
        self.assertEqual(csr.returncode, 0, csr.stderr)
        self.assertEqual(certificate.returncode, 0, certificate.stderr)
        return csr.stdout, certificate.stdout

    def test_ip_san_without_dns_alias(self):
        for text in self.generate():
            self.assertIn("DNS:" + DOMAIN, text)
            self.assertIn("IP Address:" + IP, text)
            self.assertNotIn("DNS:" + IP, text)

    def test_mixed_dns_alias_and_ip(self):
        for text in self.generate(aliases="www." + DOMAIN):
            self.assertIn("DNS:www." + DOMAIN, text)
            self.assertIn("IP Address:" + IP, text)

    def test_primary_ip_without_domain_or_alias(self):
        for text in self.generate(domain=IP):
            self.assertEqual(text.count("IP Address:" + IP), 1)
            self.assertNotIn("DNS:", text)
            self.assertNotIn("CN=" + IP, text.replace(" ", ""))

    def test_primary_ip_with_dns_alias(self):
        for text in self.generate(domain=IP, aliases=DOMAIN):
            self.assertIn("IP Address:" + IP, text)
            self.assertIn("DNS:" + DOMAIN, text)
            self.assertNotIn("DNS:" + IP, text)

    def test_multiple_ip_sans(self):
        for text in self.generate(ips=IP + ",198.18.0.126,2001:db8::1"):
            self.assertIn("IP Address:" + IP, text)
            self.assertIn("IP Address:198.18.0.126", text)
            self.assertIn("IP Address:2001:DB8:0:0:0:0:0:1", text)

    def test_key_algorithm_remains_argument_ten(self):
        for algorithm, expected in (("ecdsa-256", "prime256v1"),
                                    ("ecdsa-384", "secp384r1"),
                                    ("rsa", "Public-Key: (4096 bit)")):
            with self.subTest(algorithm=algorithm):
                for text in self.generate(algorithm=algorithm):
                    self.assertIn(expected, text)

    def test_legacy_no_ip_argument(self):
        for text in self.generate(ips=""):
            self.assertIn("DNS:" + DOMAIN, text)
            self.assertNotIn("IP Address:", text)

    def test_invalid_ip_is_rejected(self):
        result = run(str(self.command("v-generate-ssl-cert")), DOMAIN, "", "US", "CA",
                     "Test City", "Test", "Test", "", "shell", "ecdsa-256",
                     "198.18.0.125,not-an-ip", env=self.environment)
        self.assertEqual(result.returncode, 2)
        self.assertEqual(list((self.home / "runtime").iterdir()), [])

    def test_idn_main_domain_preserves_punycode_and_ip_san(self):
        for text in self.generate(domain="bücher.example.invalid"):
            self.assertIn("DNS:xn--bcher-kva.example.invalid", text)
            self.assertIn("IP Address:" + IP, text)
            self.assertNotIn("DNS:bücher", text)

    def test_idn_alias_preserves_punycode_and_ip_san(self):
        for text in self.generate(domain="bücher.example.invalid",
                                  aliases="www.bücher.example.invalid"):
            self.assertIn("DNS:xn--bcher-kva.example.invalid", text)
            self.assertIn("DNS:www.xn--bcher-kva.example.invalid", text)
            self.assertIn("IP Address:" + IP, text)


class IssuanceValidation(Fixture):
    def setUp(self):
        super().setUp()
        with (self.home / "func/main.sh").open("a") as stream:
            stream.write(f"""
E_FORBIDEN=10
is_object_valid() {{ :; }}
is_object_unsuspended() {{ :; }}
is_system_enabled() {{ :; }}
get_domain_values() {{ ALIAS='{IP}'; IP='198.18.0.126'; }}
parse_object_kv_list() {{
    ALIAS='{IP}'
    IP='198.18.0.126'
    REDIRECT='https://example.invalid'
    SSL_FORCE='yes'
}}
""")
        (self.home / "func/domain.sh").write_text("")
        (self.home / "func/ip.sh").write_text("")
        (self.home / "data/ips/198.18.0.126").write_text("OWNER='alice'\nSTATUS='dedicated'\n")
        (self.home / "data/users/alice/web.conf").write_text(f"DOMAIN='{DOMAIN}'\n")
        for command in ("v-delete-web-domain-redirect", "v-delete-web-domain-ssl-force",
                        "v-add-web-domain-redirect", "v-add-web-domain-ssl-force"):
            self.executable(command, f"#!/bin/bash\ntouch '{self.home}/mutated'\n")

    def test_invalid_ip_request_does_not_toggle_redirect_or_force_ssl(self):
        result = run(str(self.command("v-add-letsencrypt-domain")), "alice", DOMAIN, IP,
                     env=self.environment)
        self.assertEqual(result.returncode, 10, result.stdout + result.stderr)
        self.assertIn("is not assigned", result.stdout)
        self.assertFalse((self.home / "mutated").exists())

    def test_alias_membership_is_literal(self):
        path = self.home / "func/main.sh"
        with path.open("a") as stream:
            stream.write("E_NOTEXIST=3\nget_domain_values() { ALIAS='198x18x0x125'; }\n")
        result = run(str(self.command("v-add-letsencrypt-domain")), "alice", DOMAIN, IP,
                     env=self.environment)
        self.assertEqual(result.returncode, 3, result.stdout + result.stderr)
        self.assertIn("alias", result.stdout)
        self.assertFalse((self.home / "mutated").exists())


class Renewal(Fixture):
    def prepare(self, days=2, ip=True, algorithm="ecdsa-384", staging=False, domain=DOMAIN):
        (self.home / "data/ips" / IP).write_text("OWNER='alice'\nSTATUS='dedicated'\n")
        ssl = self.home / "data/users/alice/ssl"
        extensions = self.home / "extensions"
        ascii_domain = run("idn2", "--quiet", domain).stdout.strip()
        extensions.write_text(f"[SAN]\nsubjectAltName=DNS:{ascii_domain},DNS:www.{ascii_domain}"
                              + (f",IP:{IP}" if ip else "") + "\n")
        result = run("openssl", "req", "-x509", "-newkey", "ec",
                     "-pkeyopt", "ec_paramgen_curve:prime256v1", "-nodes",
                     "-keyout", str(self.home / "fixture.key"), "-out", str(ssl / (domain + ".crt")),
                     "-days", str(days), "-subj", "/CN=" + ascii_domain,
                     "-extensions", "SAN", "-config", str(extensions))
        self.assertEqual(result.returncode, 0, result.stderr)
        (self.home / "data/users/alice/web.conf").write_text(
            f"DOMAIN='{domain}' ALIAS='www.{domain},{IP}' SSL_KEY_ALGO='{algorithm}'\n")
        self.environment.update(TEST_ALIASES=f"www.{domain},{IP}", TEST_KEY_ALGO=algorithm,
                                TEST_DOMAIN=domain)
        if staging:
            (self.home / "conf/hestia.conf").write_text("LE_STAGING='yes'\n")
        self.executable("v-list-users", "#!/bin/bash\necho alice\n")
        self.executable("v-add-letsencrypt-domain", f"""#!/usr/bin/python3
import json,sys
from pathlib import Path
Path({str(self.home / "renewal.json")!r}).write_text(json.dumps(sys.argv[1:]))
""")
        for name in ("v-log-action", "v-restart-web", "v-restart-mail", "v-restart-proxy"):
            self.executable(name, "#!/bin/bash\nexit 0\n")

    def renew(self):
        result = run(str(self.command("v-update-letsencrypt-ssl")), env=self.environment)
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        path = self.home / "renewal.json"
        return json.loads(path.read_text()) if path.exists() else None

    def test_due_ip_preserves_aliases_and_key_algorithm(self):
        self.prepare()
        self.assertEqual(self.renew(), ["alice", DOMAIN, "www." + DOMAIN + "," + IP,
                                       "", "ecdsa-384"])

    def test_fresh_ip_not_renewed_every_five_minutes(self):
        self.prepare(days=6)
        self.assertIsNone(self.renew())

    def test_staging_does_not_override_shortlived_threshold(self):
        self.prepare(days=6, staging=True)
        self.assertIsNone(self.renew())

    def test_dns_only_keeps_normal_renewal_threshold(self):
        self.prepare(days=6, ip=False)
        self.assertEqual(self.renew()[2], "www." + DOMAIN)

    def test_legacy_key_algorithm_falls_back_to_rsa(self):
        self.prepare(algorithm="")
        self.assertEqual(self.renew()[4], "rsa")

    def test_removed_ip_alias_is_not_renewed(self):
        self.prepare()
        self.environment["TEST_ALIASES"] = "www." + DOMAIN
        self.assertEqual(self.renew()[2], "www." + DOMAIN)

    def test_old_ip_after_domain_move_is_not_renewed(self):
        self.prepare()
        self.environment["TEST_IP"] = "198.18.0.126"
        self.assertEqual(self.renew()[2], "www." + DOMAIN)

    def test_changed_nat_ip_is_not_renewed(self):
        self.prepare()
        (self.home / "data/ips" / IP).write_text("OWNER='alice'\nSTATUS='dedicated'\nNAT='198.18.0.126'\n")
        self.assertEqual(self.renew()[2], "www." + DOMAIN)

    def test_current_nat_ip_is_preserved(self):
        self.prepare()
        local = "192.0.2.1"
        self.environment["TEST_IP"] = local
        (self.home / "data/ips" / local).write_text(f"OWNER='alice'\nSTATUS='dedicated'\nNAT='{IP}'\n")
        self.assertIn(IP, self.renew()[2].split(","))

    def test_now_shared_ip_renews_dns_only(self):
        self.prepare()
        (self.home / "data/ips" / IP).write_text("OWNER='alice'\nSTATUS='shared'\n")
        self.assertEqual(self.renew()[2], "www." + DOMAIN)

    def test_idn_renewal_maps_dns_alias_back_and_preserves_ip(self):
        domain = "bücher.example.invalid"
        self.prepare(domain=domain)
        self.assertEqual(self.renew(), ["alice", domain, "www." + domain + "," + IP,
                                       "", "ecdsa-384"])

    def test_idn_removed_alias_is_not_resurrected(self):
        self.prepare(domain="bücher.example.invalid")
        self.environment["TEST_ALIASES"] = IP
        self.assertEqual(self.renew()[2], IP)


if __name__ == "__main__":
    unittest.main(verbosity=2)
