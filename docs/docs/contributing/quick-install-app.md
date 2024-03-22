# 快速安装应用程序

赫斯蒂亚最受欢迎的功能之一是增加对Softaculous的支持。然而，由于在hestia-php中需要使用Ioncube，并且我们反对使用专有软件，因此我们开发了自己的**快速安装应用程序**解决方案。
更多信息可在[hestia快速安装repo](https://github.com/hestiacp/hestia-quick-install/blob/main/Example/ExampleSetup.php)中找到

## 创建新应用

1.在“/usr/local/hestia/web/src/app/WebApp/Installers”中创建一个名为“Example”的新文件夹

2.创建一个名为“ExampleSetup.php”的文件。

3.复制[示例文件的内容](https://github.com/hestiacp/hestia-quick-install/blob/main/Example/ExampleSetup.php)到您的新文件中。

当您打开**快速安装应用程序**页面时，这将添加一个名为“示例”的应用程序。

## 信息

需要以下设置才能在**快速安装应用**列表上显示信息：
-名称：显示应用程序的名称。请注意，应用程序的命名应遵循以下正则表达式：`[a-zA-Z][a-zA-Z0,9]`。否则，它将不会注册为工作应用程序！
-组：目前尚未使用，但我们可能会在未来添加使用它的功能。当前使用： `cms`, `ecommerce`, `framework`。
-已启用：是否在**快速安装应用**页面中显示该应用。默认设置为`true`。
-版本： `x.x.x` 或者 `latest`。
-缩略图：应用程序图标的图像文件，包括在同一文件夹中。最大大小为300像素乘300像素。

## 设置

## #表单字段

以下字段可用：
-文本输入
-选择下拉列表
-复选框
-单选按钮
由于这是一个相当复杂的功能，请查看我们现有的应用程序以获取使用示例。
数据库
用于启用数据库自动创建的标志。如果启用，将显示一个复选框，允许用户自动创建一个新数据库，以及以下3个字段：
-数据库名称
-数据库用户
-数据库密码

## #下载应用程序的源代码

目前支持以下下载方法：
-从URL下载存档。
-通过[Composer](https://getcomposer.org).
-通过[WP-CLI](https://wp-cli.org).

## 服务器设置

使您能够设置应用程序要求和web服务器模板。例如，一些应用程序需要特定的Nginx模板，或者只能在PHP 8.0或更高版本上运行。
-Nginx：用于Nginx+PHP-FPM设置的模板。
-Apache2：用于Apache2设置的模板。通常可以省略。
-PHP版本：所有支持的PHP版本的数组。

## 安装web应用程序

下载后，有多种方法可以安装和配置web应用程序。
-配置文件的操作。
-运行命令。例如，推荐使用`drush`安装`Drupal`[github地址](https://github.com/hestiacp/hestiacp/blob/88598deb49cec6a39be4682beb8e9b8720d59c7b/web/src/app/WebApp/Installers/Drupal/DrupalSetup.php#L56-L65).或者[Drupal官方网站](drupal.org).。
-使用curl提供通过HTTP配置应用程序。

:::
警告！
为了防止出现任何问题，请确保所有命令都以用户身份执行，而不是以“root”或“admin”身份执行。默认情况下，HestiaCP提供的所有命令都会执行此操作。
:::

## 共享

完成后，您可以[提交拉取请求](https://github.com/hestiacp/hestiacp/pulls)我们将审查代码。如果它符合我们的标准，我们将在下一个版本中包括。
