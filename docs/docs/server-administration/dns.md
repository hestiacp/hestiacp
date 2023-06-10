# DNS clusters and DNSSEC

::: info
With the release of version 1.7.0, we have implemented support for DNSSEC. DNSSEC requires a Master -> Slave setup. IF the existing implementation is a Master <-> Master setup, it is not supported. DNSSEC also requires at least Ubuntu 22.04 or Debian 11!
:::

## Host your DNS on Hestia

[Create a DNS Zone](../user-guide/dns.md#adding-a-dns-zone) with the **child-ns** template, then login to your domain registrar’s panel and change the name servers of the domain. Depending your registrar panel, you could be able to create glue records. You may need to wait for up to 24 hours before the name servers become active.

## DNS Cluster setup

::: tip
Create for each server a unique user and assing them the "Sync DNS User" or "dns-cluster" role!
:::

If you are looking at options to minimise DNS-related downtime or for a way to manage DNS across all your servers, you might consider setting up a DNS cluster.

1. Whitelist your master server IP in **Configure Server** -> **Security** -> **Allowed IP addresses for API**, otherwise you will get an error when adding the slave server to the cluster.
2. Enable API access for admins (or all users).
3. Create an API key under the **admin** user with at least the **sync-dns-cluster** permission.

::: info
With the release of 1.6.0, we have implemented a new API authentication system. We strongly suggest using this method instead of the old system, as it is more secure due to the length of the access key and secret key!

If you still want to use the legacy API to authenticate with **admin** username and the password make sure **Enable legacy API** access is set to **yes**.
:::

### DNS Cluster with the Hestia API (Master <-> Master) "Default setup!"

::: warning
This method does not support DNSSEC!
:::

1. Create a new user on the Hestia server that will act as a “Slave”. Make sure it uses the username of "dns-cluster" or has the role `dns-cluster`
2. Run the following command to enable the DNS server.

```bash
v-add-remote-dns-host slave.yourhost.com 8083 'accesskey:secretkey' '' 'api' 'username'
```

Or if you still want to use admin and password authentication

```bash
v-add-remote-dns-host slave.yourhost.com 8083 'admin' 'strongpassword' 'api' 'username'
```

This way you can set up Master -> Slave or Master <-> Master <-> Master cluster.

There is no limitation on how to chain DNS servers.

### DNS Cluster with the Hestia API (Master -> Slave)

1. Create a new user on the Hestia server that will act as a “Slave”. Make sure it uses the username of "dns-user" or has the role `dns-cluster`
2. In `/usr/local/hestia/conf/hestia.conf`, change `DNS_CLUSTER_SYSTEM='hestia'` to `DNS_CLUSTER_SYSTEM='hestia-zone'`.
3. On the master server, open `/etc/bind/named.conf.options`, do the following changes, then restart bind9 with `systemctl restart bind9`.

   ```bash
   # Change this line
   allow-transfer { "none"; };
   # To this
   allow-transfer { your.slave.ip.address; };
   # Or this, if adding multiple slaves
   allow-transfer { first.slave.ip.address; second.slave.ip.address; };
   # Add this line, if adding multiple slaves
   also-notify { second.slave.ip.address; };
   ```

4. On the slave server, open `/etc/bind/named.conf.options`, do the following changes, then restart bind9 with `systemctl restart bind9`:

   ```bash
   # Change this line
   allow-recursion { 127.0.0.1; ::1; };
   # To this
   allow-recursion { 127.0.0.1; ::1; your.master.ip.address; };
   # Add this line
   allow-notify{ your.master.ip.address; };
   ```

5. Run the following command to enable the DNS server:

   ```bash
   v-add-remote-dns-host slave.yourhost.com 8083 'accesskey:secretkey' '' 'api' 'user-name'
   ```

   If you still want to use admin and password authentication:

   ```bash
   v-add-remote-dns-host slave.yourhost.com 8083 'admin' 'strongpassword' 'api' 'user-name'
   ```

### Converting an existing DNS cluster to Master -> Slave

1. In `/usr/local/hestia/conf/hestia.conf`, change `DNS_CLUSTER_SYSTEM='hestia'` to `DNS_CLUSTER_SYSTEM='hestia-zone'`.
2. On the master server, open `/etc/bind/named.options`, do the following changes, then restart bind9 with `systemctl restart bind9`.

   ```bash
   # Change this line
   allow-transfer { "none"; };
   # To this
   allow-transfer { your.slave.ip.address; };
   # Or this, if adding multiple slaves
   allow-transfer { first.slave.ip.address; second.slave.ip.address; };
   # Add this line, if adding multiple slaves
   also-notify { second.slave.ip.address; };
   ```

3. On the slave server, open `/etc/bind/named.options`, do the following changes, then restart bind9 with `systemctl restart bind9`:

   ```bash
   # Change this line
   allow-recursion { 127.0.0.1; ::1; };
   # To this
   allow-recursion { 127.0.0.1; ::1; your.master.ip.address; };
   # Add this line
   allow-notify{ your.master.ip.address; };
   ```

4. Update DNS with `v-sync-dns-cluster`

## Enabling DNSSEC

::: warning
DNSSEC can’t be used when Hestia Cluster is active as Master <-> Master
:::

To enable DNSSEC, check the checkbox in-front of DNSSEC and save.

To view the public key. Got to the list DNS domains and click the <i class="fas fas-key"></i> icon.

Depending on your registrar, you will either be able to create a new record based on the DNSKEY or based on DS key. After the DNSSEC public key has been added to the registrar, DNSSEC is enabled and live.

::: danger
Removing or disabling the private key in Hestia will make the domain inaccessble.
:::

## Can I separate DNS accounts by users

Yes, you can just supply the user variable at the end of the command.

````bash
v-add-remote-dns-host slave.yourhost.com 8083 'access_key:secret_key' '' '' 'username'```
````

or

```bash
v-add-remote-dns-host slave.yourhost.com 8083 admin p4sw0rd '' 'username'
```

With the new API system, you can also replace `api_key` with `access_key:secret_key`

::: info
By default the user `dns-cluster` or user with the role `dns-cluster` are exempted from syncing to other DNS servers!
:::

## I am not able to add a server as DNS host

When trying to add a DNS server for a cluster I get the following error:

```bash
/usr/local/hestia/func/remote.sh: line 43: return: Error:: numeric argument required
Error: api connection to slave.domain.tld failed
```

By default, API access has been disabled for non-local IP addresses. Please add your IP address to the **Allowed IP addresses for API** field in the server settings.
