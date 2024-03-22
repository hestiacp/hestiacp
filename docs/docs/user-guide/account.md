# 帐户

要访问您的帐户设置，请点击顶部的<i class="fas fa-lg fa-fw fa-user-circle"><span class="visually-hidden">用户</span></i>按钮 正确的。

## 安全

### 密码

密码要求如下：

- 至少8个字符，建议14个或更多。
- 至少 1 个号码。
- 至少 1 个大写字母和 1 个小写字母。

如果您想生成安全的密码，可以使用[1Password的生成器](https://1password.com/password-generator/)。

### 双因素身份验证 (2FA)

1. 在您的帐户设置中，选中标有“**启用双因素身份验证**”的框。
2. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。
3. 使用身份验证应用程序扫描二维码。
4. 将您的**帐户恢复代码**保存在安全的地方，以防您无法访问身份验证器。

::: 详细信息 需要 2FA 应用程序？
如果您没有身份验证器应用程序，这里有一些建议。

- iCloud 钥匙串 – [Windows](https://9to5mac.com/2022/07/25/icloud-passwords-windows-2fa-code/)、[macOS](https://9to5mac.com/2021/11/16/use-safari-password-manager-and-2fa-autofill/)，[iOS](https://9to5mac.com/2022/03/07use-ios-15-2fa-code-generator-plus-自动填充-iphone/)
- [Tofu 身份验证器](https://www.tofuauth.com/) – 开源，仅限 iOS
- [Aegis 身份验证器](https://getaegis.app/) – 开源，仅限 Android
- [Raivo OTP](https://github.com/raivo-otp/) – 开源，仅限 iOS 和 macOS
- [谷歌身份验证器](https://googleauthenticator.net/)
- [微软身份验证器](https://www.microsoft.com/en-ca/security/mobile-authenticator-app)
- [Authy](https://authy.com/) – 免费，具有云同步功能
- [1Password](https://1password.com/) – 付费密码管理器
- [Bitwarden](https://bitwarden.com/) – 密码管理器。 仅高级计划中的 2FA
- [Vaultwarden](https://docs.cloudron.io/apps/vaultwarden) (AGPL, [自托管](https://hub.docker.com/r/vaultwarden/server))，可选使用 [Bitwarden](https://linuxiac.com/how-to-install-vaultwarden-password-manager-with-docker)客户端
:::

### 登录限制

Hestia 有以下选项来帮助您保护帐户：

- 禁用帐户登录。
- 将您的 IP 地址列入白名单以登录您的帐户。

### 安全日志

安全日志包含各种信息，例如：Web 域更改、API 访问、备份创建等。单击 **<i class="fas fa-fw fa-history"></i> 日志** 按钮可查看 查看它们。

### 登录历史

在安全日志页面上，单击**<i class="fas fa-fw fa-binoscopy"></i>登录历史记录**按钮可查看登录历史记录。 历史记录包含登录时间、IP 地址和所使用的浏览器的用户代理。

## SSH 密钥

单击 **<i class="fas fa-fw fa-key"></i> 管理 SSH 密钥** 按钮查看已安装的密钥。

### 添加 SSH 密钥

1. 单击 **<i class="fas fa-fw fa-plus-circle"></i> 添加 SSH 密钥** 按钮。
2. 将您的公钥复制到文本区域中。
3. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

### 删除 SSH 密钥

1. 将鼠标悬停在要删除的 SSH 密钥上。
2. 单击 SSH ID 右侧的<i class="fas fa-fw fa-trash"><span class="visually-hidden">删除</span></i>图标。

::: 详情 需要生成密钥吗？
如果您没有 SSH 密钥，可以通过以下几种方法生成密钥。

- 对于 Windows：[Putty](https://www.ssh.com/academy/ssh/putty/windows/puttygen#running-puttygen)。
- 对于 macOS 和 Linux，请使用 `ssh-keygen`。

您还可以使用应用程序来管理它们：

- [1Password](https://developer.1password.com/docs/ssh/manage-keys/)
- [Termius](https://www.termius.com/)
:::

## API 访问密钥

::: 信息
默认情况下，对于标准用户，此选项处于禁用状态。 管理员需要在服务器设置中启用它。
:::

单击 **<i class="fas fa-fw fa-key"></i> 访问密钥** 按钮查看访问密钥。 API 使用访问密钥进行身份验证，而不是使用用户名和密码。

### 创建访问密钥

1. 单击 **<i class="fas fa-fw fa-plus-circle"></i> 添加访问密钥** 按钮。
2. 选择您要启用的权限集。
3. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。
4. 复制访问密钥和秘密密钥。 确保将密钥保存在安全的地方，因为页面关闭后**无法**查看。

### 删除访问密钥

1. 将鼠标悬停在要删除的访问密钥上。
2. 单击访问键右侧的<i class="fas fa-fw fa-trash"><span class="visually-hidden">删除</span></i>图标。
