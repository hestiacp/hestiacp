# SSL Certificates

## How to setup Let’s Encrypt for the control panel

Make sure the hostname of the server is pointed to the server’s IP address and that you set the hostname correctly.

Running the following commands will change the hostname and generate a Let’s Encrypt certificate for the control panel:

```bash
v-change-sys-hostname host.domain.tld
v-add-letsencrypt-host
```

## Managing Let's Encrypt with the cli

See CLI doc for each command:

- [v-add-letsencrypt-domain](https://hestiacp.com/docs/reference/cli.html#v-add-letsencrypt-domain)
- [v-delete-letsencrypt-domain](https://hestiacp.com/docs/reference/cli.html#v-delete-letsencrypt-domain)
- [v-add-web-domain-ssl-preset](https://hestiacp.com/docs/reference/cli.html#v-add-web-domain-ssl-preset)
- [v-add-letsencrypt-host](https://hestiacp.com/docs/reference/cli.html#v-add-letsencrypt-host)
- [v-add-cron-letsencrypt-job](https://hestiacp.com/docs/reference/cli.html#v-add-cron-letsencrypt-job)
- [v-schedule-letsencrypt-domain](https://hestiacp.com/docs/reference/cli.html#v-schedule-letsencrypt-domain)
- [v-update-letsencrypt-ssl](https://hestiacp.com/docs/reference/cli.html#v-update-letsencrypt-ssl)
- [v-add-letsencrypt-user](https://hestiacp.com/docs/reference/cli.html#v-add-letsencrypt-user)
- [v-list-letsencrypt-user](https://hestiacp.com/docs/reference/cli.html#v-list-letsencrypt-user)

## Let's Encrypt staging mode

If your test/development activites require a lot of SSL certificates, you may encounter Let's Encrypt rate limit.

- To switch to Let's Encrypt staging mode, run the cli command: `v-change-sys-config-value LE_STAGING yes`
- To resume regular Let's Encrypt mode: `v-change-sys-config-value LE_STAGING no`
- Or manually make this change in `/usr/local/hestia/conf/hestia.conf`

IMPORTANT: If switching between regular and live, manually delete: /usr/local/hestia/data/users/{user}/ssl/le.conf and user.key And you are not able to have Let's Encrypt regular and staging under the same user other wise things will break.

More info:

- [https://letsencrypt.org/docs/rate-limits/](https://letsencrypt.org/docs/rate-limits/)
- [https://letsencrypt.org/docs/staging-environment/](https://letsencrypt.org/docs/staging-environment/)

## Common errors using Let’s Encrypt

::: info
Due to changes in the code, the error message has been changed. The following list will be extended in the future.
:::

| Error         | Message                                                                                                                                              |
| ------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| `rateLimited` | The rate limit of the maximum requests have been passed. Please check [https://crt.sh](https://crt.sh) to see how many active certificates you have. |

### Let’s Encrypt validation status 400

When requesting an SSL certificate, you may encounter the following error:

```bash
Error: Let’s Encrypt validation status 400. Details: Unable to update challenge :: authorisation must be pending
```

This could mean multiple things:

1. Cloudflare’s proxy is enabled and the **SSL/TLS** setting is set to **Full (strict)**.
2. Nginx or Apache is not reloading correctly.
3. IPv6 is setup. Disable IPv6 in DNS.
4. There is an issue with a template.

In the future we hope to improve debugging, but currently the easiest way to debug this issue is to navigate to `/var/log/hestia/` and inspect the desired log file (`LE-{user}-{domain}.log`), which should appear after requesting a certificate.

Find **Step 5**, where you will see something similar to the following:

```bash
==[Step 5]==
- status: 200
- nonce: 0004EDQMty6_ZOb1BdRQSc-debiHXGXaXbZuyySFU2xoogk
- validation: pending
- details:
- answer: HTTP/2 200
server: nginx
date: Wed, 21 Apr 2021 22:32:16 GMT
content-type: application/json
content-length: 186
boulder-requester: 80260362
cache-control: public, max-age=0, no-cache
link: <https://acme-v02.api.letsencrypt.org/directory>;rel="index"
link: <https://acme-v02.api.letsencrypt.org/acme/authz-v3/12520447717>;rel="up"
location: https://acme-v02.api.letsencrypt.org/acme/chall-v3/12520447717/scDRXA
replay-nonce: 0004EDQMty6_ZOb1BdRQSc-debiHXGXaXbZuyySFU2xoogk
x-frame-options: DENY
strict-transport-security: max-age=604800

{
  "type": "http-01",
  "status": "pending",
  "url": "https://acme-v02.api.letsencrypt.org/acme/chall-v3/12520447717/scDRXA",
  "token": "9yriok5bpLtV__m-rZ8f2tQmrfeQli0tCxSj4iNkv2Y"
}
```

By following the URL in the JSON response, you will get more info about what went wrong.

### Other tips for debugging Let’s Encrypt

Try to use [Let’s Debug](https://letsdebug.net):

1. Enter your domain name.
2. Make sure HTTP-01 is selected
3. Run the test

Once the test is completed, it will show an error or a success message, containing more information.

## Can I enable Cloudflare’s proxy with Let’s Encrypt?

Yes, you are able to use Let’s Encrypt certificates with Cloudflare’s proxy, however you need to follow some special steps:

1. Disable Cloudflare’s proxy for the desired domain.
2. Wait at least 5 minutes, for DNS caches to expire.
3. Request the certificate via the control panel or use the CLI command.
4. Reenable the proxy.
5. In the **SSL/TLS** tab, switch over to **Full (strict)**.

## Can I use a Cloudflare Origin CA SSL Certificate?

1. Create an Origin CA certificate by [following these steps](https://developers.cloudflare.com/ssl/origin-configuration/origin-ca#1-create-an-origin-ca-certificate).
2. Once generated, enter your SSL keys in the **Edit Web Domain** page.
3. In the **SSL Certificate Authority / Intermediate** box, enter [this certificate](https://developers.cloudflare.com/ssl/static/origin_ca_rsa_root.pem).
4. In Cloudflare’s **SSL/TLS** tab, switch over to **Full (strict)**.
