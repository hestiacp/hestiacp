# Best Practices

Managing a server is not easy. Here are a couple of best practices you should try to adhere to while managing your Hestia server.

## Use a regular user

::: danger Never run a web or mail domain with the **admin** user
By default, the **admin** user has elevated privileges. This can pose a **security threat** to your server. For example, if you run WordPress under your **admin** user and a vulnerability is found in WordPress or a plugin, a malicious user might be able to run commands as **root**!
:::

Before adding any web or mail domain on your server, you should create a regular user. To do this, you can refer to our [User Management Guide](../user-guide/users.md#adding-a-user).

## Enable two-factor authentication (2FA) for the _admin_ user

Since the **admin** user has full control on the server, as well as elevated privileges, it is **greatly** recommended that you enable 2FA on this account. To do this, you can refer to our [Account Management](../user-guide/account.md#two-factor-authentication-2fa).
