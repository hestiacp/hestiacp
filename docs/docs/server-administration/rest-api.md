# REST API

The Hestia REST API is available to perform core functions of the Control Panel. For example, we use it internally to synchronise DNS clusters and to integrate the WHMCS billing system. The API can also be used to create new user accounts, domains, databases or even to build an alternative web interface.

The [API reference](../reference/api) provides PHP code samples demonstrating how you can integrate the API into your application or script. However, you also can use any other language to communicate with the API.

With the release of Hestia v1.6.0, we have introduced a more advanced API system and it will allow non-admin users to use specific commands.

## I’m unable to connect to the API

With the release of Hestia v1.4.0, we have decided the security needed to be tightened. If you want to connect to the API from a remote server, you will first need to whitelist its IP address. To add multiple addresses, separate them with a new line. To bypass the ip filtering, remove any existing ips and write : `allow-all`

## Can I disable the API?

Yes, you can disable the API via the server settings. The file will be deleted from the server and all connections will get ignored. Please note that some functions may not work with the API disabled.

## Password vs API key vs access keys

### Access keys

- User-specific.
- Can limit permissions. For example only `v-purge-nginx-cache`.
- Ability to disable login via other methods but still allow the use of api keys
- Can be restricted to admin user only or allowed for all users

### Password

:::danger
Method has been Deprecated
:::

- Should only be used by the admin user.
- Changing the admin password requires updating it everywhere it’s used.
- Allowed to run all commands.

### API key

:::danger
Method has been Deprecated
:::

- Should only be used by the admin user.
- Changing the admin password does not have consequences.
- Allowed to run all commands.

## Setup access/secret key authentication

To create an access key, follow [the guide in our documentation](../user-guide/account#api-access-keys).

:::tip
Or create it with the following commad. To create a acccess that requires administrator permissions create the api key via the initial admin user!
:::

```bash
v-add-access-key 'admin' 'profile' test json
```

If you want to use the api key with all commands supported use

```bash
v-add-access-key 'admin' '*' test json
```

### Creating own API key profiles

Create a new file in `/usr/local/hestia/data/api/` with the following contents

```bash
ROLE='admin'
COMMANDS='v-list-web-domains,v-add-web-domain,v-list-web-domain'
```

- Role: user or admin.
- Commands: Comma seperated list with all the command you require.

If the software you are using already supports the hash format, use `ACCESS_KEY:SECRET_KEY` instead of your old API key.

## Create an API key

::: warning
This method has been replaced by the above access/secret key authentication. We **highly** recommend using this more secure method instead.
:::

Run the command `v-generate-api-key`.

## Return codes

| Value | Name          | Comment                                                      |
| ----- | ------------- | ------------------------------------------------------------ |
| 0     | OK            | Command has been successfully performed                      |
| 1     | E_ARGS        | Not enough arguments provided                                |
| 2     | E_INVALID     | Object or argument is not valid                              |
| 3     | E_NOTEXIST    | Object doesn’t exist                                         |
| 4     | E_EXISTS      | Object already exists                                        |
| 5     | E_SUSPENDED   | Object is already suspended                                  |
| 6     | E_UNSUSPENDED | Object is already unsuspended                                |
| 7     | E_INUSE       | Object can’t be deleted because it is used by another object |
| 8     | E_LIMIT       | Object cannot be created because of hosting package limits   |
| 9     | E_PASSWORD    | Wrong / Invalid password                                     |
| 10    | E_FORBIDEN    | Object cannot be accessed by this user                       |
| 11    | E_DISABLED    | Subsystem is disabled                                        |
| 12    | E_PARSING     | Configuration is broken                                      |
| 13    | E_DISK        | Not enough disk space to complete the action                 |
| 14    | E_LA          | Server is to busy to complete the action                     |
| 15    | E_CONNECT     | Connection failed. Host is unreachable                       |
| 16    | E_FTP         | FTP server is not responding                                 |
| 17    | E_DB          | Database server is not responding                            |
| 18    | E_RDD         | RRDtool failed to update the database                        |
| 19    | E_UPDATE      | Update operation failed                                      |
| 20    | E_RESTART     | Service restart failed                                       |
