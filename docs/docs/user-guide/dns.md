# DNS

To manage your DNS zones and records, navigate to the **DNS <i class="fas fa-fw fa-atlas"></i>** tab.

## Adding a DNS zone

1. Click the **<i class="fas fa-fw fa-plus-circle"></i> Add DNS Zone** button.
2. Enter the domain name in the **Domain** field.
   - Choose the appropriate template for the zone.
   - If the domain requires different name servers, change them in the **Advanced Options** section.
3. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

## Editing a DNS zone

1. Hover over the zone you want to edit.
2. Click the <i class="fas fa-fw fa-pencil-alt"><span class="visually-hidden">edit</span></i> icon on the right of the zone’s domain.
3. Make the desired changes.
4. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

## Viewing DNSSEC public keys

1. Hover over the zone whose DNSSEC keys you want to see.
2. Click the <i class="fas fa-fw fa-key"><span class="visually-hidden">DNSSEC</span></i> icon on the right of the zone’s domain.

## Suspending a DNS zone

1. Hover over the zone you want to suspend.
2. Click the <i class="fas fa-fw fa-pause"><span class="visually-hidden">suspend</span></i> icon on the right of the zone’s domain.
3. To unsuspend it, click the <i class="fas fa-fw fa-play"><span class="visually-hidden">unsuspend</span></i> icon on the right of the zone’s domain.

## Deleting a DNS zone

1. Hover over the zone you want to delete.
2. Click the <i class="fas fa-fw fa-trash"><span class="visually-hidden">delete</span></i> icon on the right of the zone’s domain.

## DNS zone configuration

### IP address

IP address that should be used for the root domain.

### Template

- **default**: Standard DNS template. Suitable for most usecases.
- **default-nomail**: Standard DNS template. Suitable for most usecases when you don’t want to host mail on Hestia.
- **gmail**: When your email provider is Google Workspace.
- **office365**: When your email provider is Microsoft 365 (Exchange).
- **zoho**: When your email provider is Zoho.
- **child-ns**: When you are going to use the domain as a name server.

### Expiration date

This date is not used by Hestia, but can be used as a reminder.

### SOA

A Start of Authority (SOA) record includes administrative information about your zone, as defined by the domain name system (DNS).

### TTL

Adjust the default time-to-live. A shorter TTL means faster changes, but results in more requests to the server. If you are going to change the IP, it might be helpful to decrease it to 300 seconds (5 min).

### DNSSEC

Enable DNSSEC to improve security. However, this setting requires some changes to at your domain registrar before it is active. For more information, see the [DNS cluster](../server-administration/dns.md) documentation.

## Adding a DNS record to a zone

1. Click the **<i class="fas fa-fw fa-plus-circle"></i> Add Record** button.
2. Fill out the fields.
3. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

## Editing a DNS record

1. Click the recordor the <i class="fas fa-fw fa-pencil-alt"><span class="visually-hidden">edit</span></i> icon that appears on hover.
2. Make the desired changes.
3. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

## Suspending a DNS record

1. Hover over the record you want to suspend.
2. Click the <i class="fas fa-fw fa-pause"><span class="visually-hidden">suspend</span></i> icon on the right of the record’s domain.
3. To unsuspend it, click the <i class="fas fa-fw fa-play"><span class="visually-hidden">unsuspend</span></i> icon on the right of the record’s domain.

## Deleting a DNS record

1. Hover over the record you want to delete.
2. Click the <i class="fas fa-fw fa-trash"><span class="visually-hidden">delete</span></i> icon on the right of the record’s domain.

## DNS record configuration

### Record

The record name. `record`.domain.tld. Use `@` for root.

### Type

The following record types are supported:

- A
- AAAA
- CAA
- CNAME
- DNSKEY
- IPSECKEY
- KEY
- MX
- NS
- PTR
- SPF
- SRV
- TLSA
- TXT

### IP or value

IP or value of the record you want to use.

### Priority

Priority of the record. Only used for MX records

### TTL

Adjust the default time-to-live. A shorter TTL means faster changes, but results in more requests to the server. If you are going to change the IP, it might be helpful to decrease it to 300 seconds (5 min).
