# 用户

要管理用户，请以 **管理员** 身份登录并导航到 **用户 <i class="fas fa-fw fa-users"></i>** 选项卡。

## 添加用户

1. 单击 **<i class="fas fa-fw fa-plus-circle"></i> 添加用户** 按钮。
2. 填写字段。
3. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

## 冒充用户

1. 将鼠标悬停在您要登录的用户上。
2. 单击用户名右侧的<i class="fas fa-fw fa-sign-in-alt"><span class="visually-hidden">登录身份</span></i>图标 和电子邮件。
3. 您现在已作为用户登录。 因此，您执行的任何操作都将以该用户身份完成。

## 编辑用户

下面指定的设置仅适用于管理员。 常规设置可以参考【账户管理】(../user-guide/account)文档。

要编辑用户，您可以模拟他们并点击<i class="fas fa-lg fa-fw fa-user-circle"><span class="visually-hidden">用户</span></i> 右上角的图标，或按照以下步骤操作：

1. 将鼠标悬停在要编辑的用户上。
2. 单击用户姓名和电子邮件右侧的<i class="fas fa-fw fa-pencil-alt"><span class="visually-hidden">编辑</span></i>图标。

## 暂停用户

1. 将鼠标悬停在要暂停的用户上。
2. 单击用户姓名和电子邮件右侧的<i class="fas fa-fw fa-pause"><span class="visually-hidden">暂停</span></i>图标。

## 删除用户

1. 将鼠标悬停在要删除的用户上。
2. 单击用户姓名和电子邮件右侧的<i class="fas fa-fw fa-trash"><span class="visually-hidden">删除</span></i>图标。

## 用户配置

### 禁用控制面板访问

要删除用户的控制面板访问权限，请选中标记为：**不允许用户登录控制面板**的框。

### 改变角色

要更改用户的角色，请更改下拉列表中的 **角色** 值。

::: 警告
将 **管理员** 角色分配给用户将使他们能够查看和编辑其他用户。 他们将无法编辑 **admin** 用户，但能够看到它们，除非在服务器设置中禁用。
:::

### 更换套餐

要更改用户的包，请更改下拉列表中的 **Package** 值。

### 更改 SSH 访问

要更改用户的 SSH 访问权限，请单击 **高级选项** 按钮，然后从下拉列表中更改 **SSH 访问权限** 值。

::: 警告
使用 **nologin** shell 不会禁用 SFTP 访问。
:::

### 更改 PHP CLI 版本

要更改用户的 PHP CLI 版本，请单击 **高级选项** 按钮，然后从下拉列表中更改 **PHP CLI 版本** 值。

### 更改默认名称服务器

要更改用户的默认名称服务器，请单击 **高级选项** 按钮，然后编辑 **默认名称服务器** 字段。

::: 警告
至少需要 2 个默认名称服务器。 这是为了提供冗余，以防其中一个无法回答。 事实上，建议两个名称服务器位于不同的服务器上，以获得更好的弹性。 如果您是系统管理员并且想要进行设置，请参阅我们的 [DNS 集群文档](../server-administration/dns#dns-cluster-setup)。
:::
