# DNS clusters and DNSSEC

::: info
With the release of version 1.7.0, we have implemented support for DNSSEC. DNSSEC requires a Master -> Slave setup. IF the existing implementation is a Master <-> Master setup, it is not supported. DNSSEC also requires at least Ubuntu 22.04 or Debian 11!
:::

## Host DNS for your domain on Hestia

Pre-requisites

These steps require that you configure the DNS servers of your domain to use your Hestia servers.

- Note that most domain providers require two or more DNS servers to be configured.
- The name servers will most likely be required to be registered as 'Glue records'
- You may need to wait for up to 24 hours before the name servers become available

Preparing the domain and DNS

1. On your Hestia master, [create a DNS Zone](../user-guide/dns#adding-a-dns-zone) with the **child-ns** template
2. On your domain registrar panel, set the name servers of the domain to the Hestia servers

If you are looking at options to minimise DNS-related downtime or for a way to automatically synchronise DNS zones across all your servers, you might consider setting up a DNS cluster.

If DNSSEC matters to you, then you must use Master -> Slave. However if you would like to add zones to either server and have them replicate to the other, then configure as Master <-> Master.

::: tip
If you have just set up your slave, check that the host name resolves and that you have a valid SSL certificate
:::

## DNS Cluster setup

A Master server is where DNS zones are created, and a Slave server recieves the zone via the API. Hestia can be configured as Master <-> Master or Master -> Slave. With a Master <-> Master configuration, each Master is also a Slave, so it could be considered as Master/Slave <-> Master/Slave.

On each Slave server, a unique user is required who will be assigned the zones, and must be assigned the "Sync DNS User" or "dns-cluster" role.

::: info
With the release of 1.6.0, we have implemented a new API Access Key authentication system. We strongly suggest using this method instead of the previous username/password system, as it is more secure due to the length of the access key and secret key!

If you still want to use the legacy API to authenticate with **admin** username and the password make sure **Enable legacy API** access is set to **yes**.
:::

### Master <-> Master DNS cluster (Default setup) with the Hestia API

::: warning
This method does not support DNSSEC!
:::

1. Create a new user on each Hestia server that will act as a “Slave”. Make sure it uses the username of "dns-cluster" or has the role `dns-cluster`
2. Run the following command to enable the DNS server.

```bash
v-add-remote-dns-host slave.yourhost.com 8083 'accesskey:secretkey' '' 'api' 'username'
```

Or if you still want to use admin and password authentication (not recommended)

```bash
v-add-remote-dns-host slave.yourhost.com 8083 'admin' 'strongpassword' 'api' 'username'
```

This way you can set up Master -> Slave or Master <-> Master <-> Master cluster.

There is no limitation on how to chain DNS servers.

### Master -> Slave DNS cluster with the Hestia API

::: info
It doesn't work if you try to sync via local network! See [Issue](https://github.com/hestiacp/hestiacp/issues/4295) Make sure to use the public ip addresses
:::

Preparing your **Slave** server(s):

1. Whitelist your master server IP in **Configure Server** -> **Security** -> **Allowed IP addresses for API**
2. Enable API access for admins (or all users).
3. Create an API key under the **admin** user with at least the **sync-dns-cluster** permission. This is found in user settings / Access keys.
4. Create a new DNS sync user as follows:
   - Has email address (something generic)
   - Has the role `dns-cluster`
   - You may want to set 'Do not allow user to log in to Control Panel' if they are not a regular user
   - If you have more than one slave, the slave user must be unique
5. Edit `/usr/local/hestia/conf/hestia.conf`, change `DNS_CLUSTER_SYSTEM='hestia'` to `DNS_CLUSTER_SYSTEM='hestia-zone'`.
6. Edit `/etc/bind/named.conf.options`, do the following changes, then restart bind9 with `systemctl restart bind9`:

   ```bash
   # Change this line
   allow-recursion { 127.0.0.1; ::1; };
   # To this
   allow-recursion { 127.0.0.1; ::1; your.master.ip.address; };
   # Add this line
   allow-notify{ your.master.ip.address; };
   ```

Preparing your **Master** server:

1. On the **Master** server, open `/usr/local/hestia/conf/hestia.conf`, change `DNS_CLUSTER_SYSTEM='hestia'` to `DNS_CLUSTER_SYSTEM='hestia-zone'`.
2. Edit `/etc/bind/named.conf.options`, do the following changes, then restart bind9 with `systemctl restart bind9`.

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

3. Run the following command to enable each Slave DNS server, and wait a short while for it to complete zone transfers:

   ```bash
   v-add-remote-dns-host <your slave host name> <port number> '<accesskey>:<secretkey>' '' 'api' '<your chosen slave user name>'
   ```

   If you still want to use admin and password authentication (not recommended):

   ```bash
   v-add-remote-dns-host slave.yourhost.com 8083 'admin' 'strongpassword' 'api' 'user-name'
   ```

4. Check it worked by listing the DNS zones on the **Slave** for the dns-user with the CLI command `v-list-dns-domains dns-user` or by connecting to the web interface as dns-user and reviewing the DNS zones.

### Converting an existing DNS cluster to Master -> Slave

1. On **Master** and **Slave** servers, open `/usr/local/hestia/conf/hestia.conf`, change `DNS_CLUSTER_SYSTEM='hestia'` to `DNS_CLUSTER_SYSTEM='hestia-zone'`.
2. On the **Master** server, open `/etc/bind/named.conf.options`, do the following changes, then restart bind9 with `systemctl restart bind9`.

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

3. On the **Slave** server, open `/etc/bind/named.conf.options`, do the following changes, then restart bind9 with `systemctl restart bind9`:

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

## FAQ & troubleshooting

### Can I separate DNS accounts by users

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

### I am not able to add a server as DNS host

When trying to add a DNS server for a cluster I get the following error:

```bash
/usr/local/hestia/func/remote.sh: line 43: return: Error:: numeric argument required
Error: api connection to slave.domain.tld failed
```

By default, API access is disabled for non-local IP addresses. On your **Slave**, add the IP address of your **Master** to the **Allowed IP addresses for API** field in Server settings -> Configure -> Security -> System -> Allowed IP addresses for API and press Save.
