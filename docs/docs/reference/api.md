# API

::: 信息
此页面正在进行中。 很多信息将会丢失。
:::

＃＃ 示例

示例可以在单独的[repo](https://github.com/hestiacp/hestiacp-api-examples)中找到。

## 从用户名/密码身份验证升级到访问/密钥身份验证

替换以下代码:

```php
// Prepare POST query
$postvars = [
	"user" => $hst_username,
	"password" => $hst_password,
	"returncode" => $hst_returncode,
	"cmd" => $hst_command,
	"arg1" => $username,
];
```

具有以下内容:

```php
// Prepare POST query
$postvars = [
	"hash" => "access_code:secret_code",
	"returncode" => $hst_returncode,
	"cmd" => $hst_command,
	"arg1" => $username,
];
```
