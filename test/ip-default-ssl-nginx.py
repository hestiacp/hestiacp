#!/usr/bin/env python3
"""Real standalone-nginx listener tests, isolated from installed services.

Run: python3 test/ip-default-ssl-nginx.py --nginx /usr/sbin/nginx
Requires the same dependencies as ip-default-ssl.py and an nginx executable.
Uses loopback ephemeral ports; this is not a standalone-Hestia installation test.
"""
from pathlib import Path
import argparse
import grp
import importlib.util
import os
import pwd
import shutil
import socket
import ssl
import subprocess
import sys
import time
import unittest

sys.dont_write_bytecode = True
ROOT = Path(__file__).resolve().parents[1]
spec = importlib.util.spec_from_file_location("ip_default_fixture", ROOT / "test/ip-default-ssl.py")
fixture_module = importlib.util.module_from_spec(spec)
spec.loader.exec_module(fixture_module)
IP = fixture_module.IP
DOMAIN = fixture_module.DOMAIN
NGINX = None


class NativeStandalone(unittest.TestCase):
    def setUp(self):
        self.fixture = fixture_module.DefaultCertificate(methodName="runTest")
        self.addCleanup(self.fixture.doCleanups)
        self.fixture.setUp()
        fixture = self.fixture
        self.home = fixture.home
        fixture.settings.write_text("ROOT_USER='admin'\nWEB_SYSTEM='nginx'\nPROXY_SYSTEM=''\n")
        with socket.socket() as reserved:
            reserved.bind(("127.0.0.1", 0))
            self.port = reserved.getsockname()[1]
        bob = self.home / "data/users/bob/ssl"
        fixture.original = f"""server {{
    listen 127.0.0.1:{self.port} default_server ssl;
    server_name _;
    ssl_certificate {bob}/{DOMAIN}.pem;
    ssl_certificate_key {bob}/{DOMAIN}.key;
    return 404;
}}
"""
        fixture.conf.write_text(fixture.original)
        # Emulate Hestia's separate home-directory certificate copies.
        self.site = self.home / "native-site"
        self.site.mkdir()
        for extension in ("pem", "key"):
            shutil.copyfile(self.home / f"data/users/alice/ssl/{DOMAIN}.{extension}",
                            self.site / ("certificate." + extension))
        site_conf = self.home / "native-site.conf"
        site_conf.write_text(f"""server {{
    listen 127.0.0.1:{self.port} ssl;
    server_name {DOMAIN} {IP};
    ssl_certificate {self.site}/certificate.pem;
    ssl_certificate_key {self.site}/certificate.key;
    return 200 "selected-ip-site";
}}
""")
        self.configuration = self.home / "native-nginx.conf"
        worker = pwd.getpwnam("nobody") if os.geteuid() == 0 else pwd.getpwuid(os.geteuid())
        group = grp.getgrgid(worker.pw_gid).gr_name
        self.configuration.write_text(f"""user {worker.pw_name} {group};
pid {self.home}/native-nginx.pid;
error_log {self.home}/native-error.log;
events {{ worker_connections 32; }}
http {{
    access_log off;
    ssl_session_cache off;
    client_body_temp_path {self.home}/client-temp;
    proxy_temp_path {self.home}/proxy-temp;
    fastcgi_temp_path {self.home}/fastcgi-temp;
    uwsgi_temp_path {self.home}/uwsgi-temp;
    scgi_temp_path {self.home}/scgi-temp;
    include {fixture.conf};
    include {site_conf};
}}
""")
        self.nginx_args = [NGINX, "-e", str(self.home / "startup-error.log"),
                           "-p", str(self.home), "-c", str(self.configuration)]
        fixture.executable(self.home / "shim/nginx", f"""#!/usr/bin/python3
import os,sys
os.execv({NGINX!r}, {self.nginx_args!r} + sys.argv[1:])
""")
        result = subprocess.run(self.nginx_args,
                                capture_output=True, text=True, timeout=10)
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        self.addCleanup(self.stop_nginx)

    def stop_nginx(self):
        subprocess.run([*self.nginx_args, "-s", "quit"],
                       capture_output=True, timeout=10, check=True)
        # Wait for this test-owned master to close its listener before removing
        # the fixture; do not leave an nginx process holding deleted files.
        deadline = time.monotonic() + 5
        while (self.home / "native-nginx.pid").exists() and time.monotonic() < deadline:
            time.sleep(0.02)
        self.assertFalse((self.home / "native-nginx.pid").exists())

    def request(self, owner):
        context = ssl.SSLContext(ssl.PROTOCOL_TLS_CLIENT)
        context.load_verify_locations(str(self.home / f"data/users/{owner}/ssl/{DOMAIN}.pem"))
        context.verify_flags |= ssl.VERIFY_X509_PARTIAL_CHAIN
        # Python/OpenSSL omits SNI for an IP literal while still checking IP SAN.
        deadline = time.monotonic() + 5
        last_error = None
        while time.monotonic() < deadline:
            try:
                with socket.create_connection(("127.0.0.1", self.port), timeout=2) as raw:
                    with context.wrap_socket(raw, server_hostname=IP) as connection:
                        connection.sendall(
                            f"GET / HTTP/1.1\r\nHost: {IP}\r\nConnection: close\r\n\r\n".encode())
                        data = b""
                        while chunk := connection.recv(4096):
                            data += chunk
                        self.assertIn(b"200 OK", data)
                        self.assertTrue(data.endswith(b"selected-ip-site"), data)
                        return
            except (ssl.SSLError, OSError) as error:
                last_error = error
                time.sleep(0.02)
        self.fail(f"No verified no-SNI response for {owner}: {last_error}")

    def test_selection_switch_and_reset_with_real_nginx(self):
        self.request("bob")
        result = self.fixture.sync()
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        self.request("alice")
        result = self.fixture.helper(f"release_ip_default_ssl alice '{DOMAIN}' '{IP}'")
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        self.request("bob")
        self.assertEqual(self.fixture.conf.read_text(), self.fixture.original)

    def test_real_configtest_rejects_bad_key_and_rolls_back(self):
        before = self.fixture.conf.read_bytes(), self.fixture.record.read_bytes()
        shutil.copyfile(self.home / f"data/users/bob/ssl/{DOMAIN}.key",
                        self.home / f"data/users/alice/ssl/{DOMAIN}.key")
        result = self.fixture.select(success=False)
        self.assertIn("configuration test failed", result.stdout.lower())
        self.assertEqual((self.fixture.conf.read_bytes(), self.fixture.record.read_bytes()), before)
        self.assertEqual(list((self.home / "data/ip-default-ssl").iterdir()), [])
        self.request("bob")

    def test_primary_ip_needs_no_alias(self):
        user_data = self.home / "data/users/alice"
        for ext in ("pem", "key"):
            shutil.copyfile(user_data / f"ssl/{DOMAIN}.{ext}", user_data / f"ssl/{IP}.{ext}")
        (user_data / "web.conf").write_text(
            f"DOMAIN='{IP}' IP='{IP}' ALIAS='' SSL='yes' SUSPENDED='no'\n")
        result = self.fixture.helper(f"user=alice; USER_DATA='{user_data}'; domain='{IP}'\n"
                                     'source "$HESTIA/func/domain.sh"\nsync_dedicated_ip_https')
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        self.request("alice")
        result = self.fixture.helper(f"release_ip_default_ssl alice '{IP}'")
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        self.request("bob")


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--nginx", default=shutil.which("nginx"),
                        help="nginx executable; no installed config is loaded")
    args, remaining = parser.parse_known_args()
    if not args.nginx:
        parser.error("nginx is required; supply --nginx")
    NGINX = str(Path(args.nginx).resolve())
    unittest.main(argv=[sys.argv[0], *remaining], verbosity=2)
