# HestiaCP IPv6 Support — Implementation & Roadmap

## What This PR Implements

This pull request adds full IPv6 support to HestiaCP, building on the groundwork laid by [@coriaweb in PR #4996](https://github.com/hestiacp/hestiacp/pull/4996) and extending it with a complete web panel integration that was missing from the original proposal.

### Changes Included

**Core functions:**
- `func/main.sh` — Replaces PHP-based config parser with a pure bash implementation (`hst_parse_config_string`) using `setpriv` for sandboxed eval. Adds `hst_detect_ip_version()` for unified IPv4/IPv6 detection.
- `func/ip.sh` — Adds IPv6-specific helper functions: `get_user_ip6s()`, `get_user_ipv6()`, `get_ipv6_iface()`, `is_ipv6_valid()`. Generalizes existing IPv4 functions to accept any IP version.

**IP management:**
- `bin/v-add-sys-ip` — Now accepts both IPv4 and IPv6 addresses using the same command. Supports CIDR notation (`2001:db8::1/64`) and prefix length (`/64`).
- `bin/v-add-web-domain-ipv46` — New dual-stack domain creation script. Accepts one IPv4 and one IPv6 independently.
- `bin/v-change-web-domain-ip` — Routes IPv6 addresses to `v-change-web-domain-ipv6` automatically.
- `bin/v-change-web-domain-ipv6` — New script to assign or update the IPv6 address of a web domain.
- `bin/v-change-dns-domain-ipv6` — New script to manage AAAA records.

**Web panel:**
- `/list/ip/` — Now displays both IPv4 and IPv6 addresses with appropriate column labels.
- `/add/ip/` — Accepts IPv4 or IPv6 in the IP field. Netmask/prefix length is optional for IPv6 when embedded in the address.
- `/edit/web/` — **Two separate dropdowns**: one for IPv4 (single select), one for IPv6 (single select). Each defaults to the currently assigned address.

**Firewall panel (full IPv6 management UI):**
- Complete `/list/firewall/ipv6/` panel mirroring the IPv4 firewall interface.
- Full CRUD: add, edit, delete, move, suspend, unsuspend rules.
- Banlist management for IPv6 IPs.
- Fail2ban integration via `hestia6` action and mirrored jail configuration.

---

## Current Limitation: Single IP Per Type Per Domain

The current implementation supports **one IPv4 and one IPv6 address per domain**, stored as:

```
# /usr/local/hestia/data/users/<user>/web.conf
IP='203.0.113.10' IP6='2001:db8:1::1' ...
```

This enables dual-stack hosting but limits each domain to a single address per protocol version.

---

## Architectural Vision: Multi-Stack IP Assignment

### Proposed UI

The ideal interface for IP assignment would expose three independent sections:

```
┌─ IPv4 Address ──────────────────┐  ┌─ IPv6 Address ──────────────────┐
│ ○ None                          │  │ ○ None                          │
│ ● 203.0.113.10                  │  │ ● 2001:db8:1::1                 │
│ ○ 203.0.113.11                  │  │ ○ 2001:db8:1::2                 │
└─────────────────────────────────┘  └─────────────────────────────────┘

┌─ Multi-Stack (advanced) ────────────────────────────────────────────────┐
│  Select any combination of IPv4 and IPv6 addresses for this domain:    │
│                                                                         │
│  ☐ 203.0.113.10   (IPv4)                                               │
│  ☑ 203.0.113.11   (IPv4)                                               │
│  ☑ 2001:db8:1::1  (IPv6)                                               │
│  ☐ 2001:db8:1::2  (IPv6)                                               │
└─────────────────────────────────────────────────────────────────────────┘
```

This would allow:
- IPv4-only domain (select IPv4, IPv6 = None)
- IPv6-only domain (IPv4 = None, select IPv6)
- Dual-stack (select one of each)
- Multi-stack (select multiple of any type via checkboxes)

### What Nginx Would Generate

For a domain with `203.0.113.11` and `2001:db8:1::1` selected:

```nginx
server {
    listen 203.0.113.11:80;
    listen [2001:db8:1::1]:80;
    server_name example.com www.example.com;
    ...
}
```

For IPv6-only:

```nginx
server {
    listen [2001:db8:1::1]:80;
    server_name example.com;
    ...
}
```

### Why This Requires a Separate PR

Implementing multi-stack IP assignment requires changes beyond the scope of this PR:

1. **`web.conf` format change** — `IP` and `IP6` would need to support comma-separated lists or a new field format (e.g., `IPS='203.0.113.10,203.0.113.11'`).

2. **Nginx/Apache template engine** — The current template substitution uses a single `directIP` placeholder. Supporting multiple IPs requires iterating over a list and generating multiple `listen` directives.

3. **All rebuild scripts** — `v-rebuild-web-domain`, `v-rebuild-user`, `v-rebuild-all` and related scripts iterate over domain configs and assume a single IP per field.

4. **DNS management** — Adding multiple A/AAAA records automatically for multi-stack domains.

5. **SSL/Let's Encrypt** — Certificate validation across multiple IPs.

These changes would touch approximately 20+ additional files and fundamentally alter how HestiaCP manages network bindings.

### Proposal

We suggest this PR be accepted as the foundation for IPv6 support, and that multi-stack IP assignment be tracked as a follow-up enhancement once the single-IPv6 implementation is stable in production.

If the HestiaCP team is open to the multi-stack direction, we are willing to implement it as a subsequent PR following the architecture described here.

---

## Testing

Tested on Ubuntu 22.04 LTS with HestiaCP v1.9.6, AlphaVPS hosting environment.

**Add IPv4:**
```bash
v-add-sys-ip 203.0.113.10 255.255.255.0 eth0 admin shared
```

**Add IPv6:**
```bash
v-add-sys-ip 2001:db8:1::1 /64 eth0 admin shared
```

**Add domain with dual-stack:**
```bash
v-add-web-domain-ipv46 admin example.com 203.0.113.10 2001:db8:1::1
```

**Change domain to IPv6:**
```bash
v-change-web-domain-ipv6 admin example.com 2001:db8:1::1
```
