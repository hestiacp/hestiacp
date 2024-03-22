# 定制

::: 警告
我们目前只支持通过CSS更改布局。您可以自定义HTML文件和模板，但它们会在更新过程中被覆盖，因此请确保[设置更新脚本策略](# 更新升级运行脚本命令)以在更新后恢复您的更改。
:::

## 添加新主题

在中创建新主题`/usr/local/hestia/web/css/theme/custom/my_theme.css`

```css
.body-login,
.body-reset {
	height: auto;
	padding-top: 10%;
	background: rgb(231, 102, 194) !important;
	background: radial-gradient(circle, rgba(231, 102, 197, 1), rgba(174, 43, 177, 1)) !important;
}
```

## 自定义默认主题

对默认主题的更改在更新期间始终会被覆盖。 自定义 CSS 文件可以以 `.css` 或 `.min.css` 格式上传到 `/usr/local/hestia/web/css/custom`。

请注意，始终加载`default.css`基本主题。 其他默认和自定义主题会覆盖此文件中的规则。

## 自定义_未找到域名_页面

“找不到域名”页面位于`/var/www/html/index.html`。 您可以使用以下命令对其进行编辑：

```bash
nano /var/www/html/index.html
```

## 自定义默认域生成的配置文件

创建域时将添加到域的默认结构位于`/usr/local/hestia/data/templates/web/skel`中。

## 更新升级运行脚本命令

随着 Hestia 1.4.6 的发布，我们添加了更新文件配置。 例如，您可以使用脚本文件来修改定义更新哪些文件配置：

- 更新前后禁用和启用配置。
- 恢复自定义架构页面。

脚本位于以下位置的文件之一：

- `/etc/hestiacp/hooks/pre_install.sh`
- `/etc/hestiacp/hooks/post_install.sh`

::: 注意
不要忘记通过运行“chmod +x /etc/hestiacp/hooks/[file name].sh”给文件赋予执行权限，使文件可执行。
:::

例如，要在预安装更新时禁用默认配置：

```bash /etc/hestiacp/hooks/pre_install.sh
#!/bin/bash
sed -i "s|^DEMO_MODE=.*'|DEMO_MODE='no'|g" $HESTIA/conf/hestia.conf
```

::: 警告
如果您使用自定义修改出现错误文件，您将不得不再次重建所有网站！
:::
