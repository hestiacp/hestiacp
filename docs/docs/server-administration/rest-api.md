# API 系统介绍

Hestia REST API 可用于执行控制面板的核心功能。 例如，我们在内部使用它来同步 DNS 集群并集成 WHMCS 计费系统。 该 API 还可用于创建新的用户帐户、域、数据库，甚至构建替代的 Web 界面。

[API 参考](../reference/api) 提供 PHP 代码示例，演示如何将 API 集成到应用程序或脚本中。 但是，您也可以使用任何其他语言与 API 进行通信。

随着 Hestia v1.6.0 的发布，我们引入了更先进的 API 系统，它将允许非管理员用户使用特定命令。

## 我无法连接到 API

随着 Hestia v1.4.0 的发布，我们决定需要加强安全性。 如果您想从远程服务器连接到 API，您首先需要将其 IP 地址列入白名单。 要添加多个地址，请用新行分隔它们。

## 我可以禁用 API 吗？

是的，您可以通过服务器设置禁用 API。 该文件将从服务器中删除，所有连接都将被忽略。 请注意，禁用 API 后某些功能可能无法使用。

## 密码 vs API 密钥 vs 访问密钥

＃＃＃ 密码

- 只能由管理员用户使用。
- 更改管理员密码需要在使用该密码的所有地方进行更新。
- 允许运行所有命令。

### API 密钥

- 只能由管理员用户使用。
- 更改管理员密码不会产生任何后果。
- 允许运行所有命令。

### 访问键

- 用户特定。
- 可以限制权限。 例如仅`v-purge-nginx-cache`。
- 能够禁用通过其他方法登录，但仍然允许使用 api 密钥
- 可以仅限于管理员用户或允许所有用户。

## 设置访问/密钥认证

要创建访问密钥，请遵循[我们文档中的指南](../user-guide/account#api-access-keys)。

如果您使用的软件已经支持哈希格式，请使用“ACCESS_KEY:SECRET_KEY”而不是旧的 API 密钥。

## 创建 API 密钥

：：： 警告
该方法已被上述访问/密钥认证所取代。 我们**强烈**建议使用这种更安全的方法。
:::

运行命令`v-generate-api-key`。

## 返回代码

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
