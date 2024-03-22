# hestiacp官方建议

管理服务器并不容易。 以下是您在管理 Hestia 服务器时应尝试遵循的一些最佳实践。

## 使用普通用户

::: danger 切勿使用 **admin** 用户运行 Web 或邮件域
默认情况下，**admin** 用户具有提升的权限。 这可能会对您的服务器构成**安全威胁**。 例如，如果您在 **admin** 用户下运行 WordPress，并且在 WordPress 或插件中发现漏洞，则恶意用户可能能够以 **root** 身份运行命令！
:::

在服务器上添加任何 Web 或邮件域之前，您应该创建一个普通用户。 为此，您可以参考我们的[用户管理指南](../user-guide/users#adding-a-user)。

## 为_admin_用户启用双因素身份验证（2FA）

由于 **admin** 用户对服务器拥有完全控制权以及提升的权限，因此**强烈**建议您在此帐户上启用 2FA。 为此，您可以参考我们的[账户管理](../user-guide/account#two-factor-authentication-2fa)。
