# SSL 证书

## 如何为控制面板设置 Let’s Encrypt

确保服务器的主机名指向服务器的 IP 地址，并且主机名设置正确。

运行以下命令将更改主机名并为控制面板生成 Let’s Encrypt 证书：

```bash
v-change-sys-主机名主机.domain.tld
v-add-letsencrypt-主机
```

## 使用 Let’s Encrypt 的常见错误

::: 信息
由于代码的更改，错误消息已更改。 以下列表将来将会扩展。
:::

| 错误          | 提示                                                                                                                                      |
| ------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| `rateLimited` | 已超过最大请求的速率限制。 请检查 [https://crt.sh](https://crt.sh) 查看您拥有多少个有效证书. |

### 让我们加密验证状态 400

申请SSL证书时，您可能会遇到以下错误：

```bash
Error: Let’s Encrypt validation status 400. Details: Unable to update challenge :: authorisation must be pending
```

这可能意味着很多事情：

1. Cloudflare 的代理已启用，并且 **SSL/TLS** 设置设为 **完整（严格）**。
2. Nginx 或 Apache 未正确重新加载。
3. IPv6 已设置。 在 DNS 中禁用 IPv6。
4. 模板有问题。

将来我们希望改进调试，但目前调试此问题的最简单方法是导航到`/var/log/hestia/`并检查所需的日志文件（`LE-{user}-{domain}.log`）它应该在请求证书后出现。

找到**步骤 5**，您将在其中看到类似以下内容的内容：

```bash
==[Step 5]==
- status: 200
- nonce: 0004EDQMty6_ZOb1BdRQSc-debiHXGXaXbZuyySFU2xoogk
- validation: pending
- details:
- answer: HTTP/2 200
server: nginx
date: Wed, 21 Apr 2021 22:32:16 GMT
content-type: application/json
content-length: 186
boulder-requester: 80260362
cache-control: public, max-age=0, no-cache
link: <https://acme-v02.api.letsencrypt.org/directory>;rel="index"
link: <https://acme-v02.api.letsencrypt.org/acme/authz-v3/12520447717>;rel="up"
location: https://acme-v02.api.letsencrypt.org/acme/chall-v3/12520447717/scDRXA
replay-nonce: 0004EDQMty6_ZOb1BdRQSc-debiHXGXaXbZuyySFU2xoogk
x-frame-options: DENY
strict-transport-security: max-age=604800

{
  "type": "http-01",
  "status": "pending",
  "url": "https://acme-v02.api.letsencrypt.org/acme/chall-v3/12520447717/scDRXA",
  "token": "9yriok5bpLtV__m-rZ8f2tQmrfeQli0tCxSj4iNkv2Y"
}
```

通过跟踪 JSON 响应中的 URL，您将获得有关问题所在的更多信息。

### 调试 Let’s Encrypt 的其他技巧

尝试使用[Let’s Debug](https://letsdebug.net)：

1. 输入您的域名。
2. 确保选择了 HTTP-01
3. 运行测试

测试完成后，它将显示错误或成功消息，其中包含更多信息。

## 我可以通过 Let’s Encrypt 启用 Cloudflare 的代理吗？

是的，您可以通过 Cloudflare 代理使用 Let’s Encrypt 证书，但是您需要遵循一些特殊步骤：

1. 禁用所需域的 Cloudflare 代理。
2. 等待至少 5 分钟，让 DNS 缓存过期。
3. 通过控制面板或使用 CLI 命令请求证书。
4. 重新启用代理。
5. 在 **SSL/TLS** 选项卡中，切换到 **完整（严格）**。

## 我可以使用 Cloudflare Origin CA SSL 证书吗？

1. [按照以下步骤](https://developers.cloudflare.com/ssl/origin-configuration/origin-ca#1-create-an-origin-ca-certificate) 创建 Origin CA 证书。
2. 生成后，在 **编辑 Web 域** 页面中输入您的 SSL 密钥。
3. 在**SSL 证书颁发机构/中级**框中，输入[此证书](https://developers.cloudflare.com/ssl/static/origin_ca_rsa_root.pem)。
4. 在 Cloudflare 的 **SSL/TLS** 选项卡中，切换到 **完整（严格）**
