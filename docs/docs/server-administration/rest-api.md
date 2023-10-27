# REST API

The Hestia REST API is available to perform core functions of the Control Panel. For example, we use it internally to synchronise DNS clusters and to integrate the WHMCS billing system. The API can also be used to create new user accounts, domains, databases or even to build an alternative web interface.

The [API reference](../reference/api) provides PHP code samples demonstrating how you can integrate the API into your application or script. However, you also can use any other language to communicate with the API.

With the release of Hestia v1.6.0, we have introduced a more advanced API system and it will allow non-admin users to use specific commands.

## I’m unable to connect to the API

With the release of Hestia v1.4.0, we have decided the security needed to be tightened. If you want to connect to the API from a remote server, you will first need to whitelist its IP address. To add multiple addresses, separate them with a new line.

## Can I disable the API?

Yes, you can disable the API via the server settings. The file will be deleted from the server and all connections will get ignored. Please note that some functions may not work with the API disabled.

## Password vs API key vs access keys

### Password

- Should only be used by the admin user.
- Changing the admin password requires updating it everywhere it’s used.
- Allowed to run all commands.

### API key

- Should only be used by the admin user.
- Changing the admin password does not have consequences.
- Allowed to run all commands.

### Access keys

- User-specific.
- Can limit permissions. For example only `v-purge-nginx-cache`.
- Ability to disable login via other methods but still allow the use of api keys
- Can be restricted to admin user only or allowed for all users.

## Setup access/secret key authentication

To create an access key, follow [the guide in our documentation](../user-guide/account#api-access-keys).

If the software you are using already supports the hash format, use `ACCESS_KEY:SECRET_KEY` instead of your old API key.

## Create an API key

::: warning
This method has been replaced by the above access/secret key authentication. We **highly** recommend using this more secure method instead.
:::

Run the command `v-generate-api-key`.

## Return codes

[Reference: Return codes](../reference/return-codes)
