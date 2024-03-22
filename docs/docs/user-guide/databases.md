# 数据库

要管理数据库，请导航至 **DB <i class="fas fa-fw fa-database"></i>** 选项卡。

## 添加数据库

1. 单击 **<i class="fas fa-fw fa-plus-circle"></i> 添加数据库** 按钮。
2. 填写字段。 名称和用户名将以`user_`为前缀。
3. （可选）提供用于发送登录详细信息的电子邮件地址。
4. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

在`高级选项`下，您可以选择主机（默认为`localhost`）和字符集（默认为`utf8`）。

## 编辑数据库

1. 将鼠标悬停在要编辑的数据库上。
2. 单击数据库名称右侧的<i class="fas fa-fw fa-pencil-alt"><span class="visually-hidden">编辑</span></i>图标。 如果您不想更改密码，请将密码字段保留为空。

## 访问数据库

默认情况下，可分别通过`https://hostname.domain.tld/phpmyadmin`和`https://hostname.domain.tld/phppgadmin`访问 **phpMyAdmin** 和 **phpPgAdmin**。 您还可以点击 **<i class="fas fa-fw fa-database"></i> phpMyAdmin** 和 **<i class="fas fa-fw fa-database"></i> phpPgAdmin** **DB <i class="fas fa-fw fa-database"></i>** 选项卡中的按钮。

对于 MySQL 数据库，如果启用 **phpMyAdmin 单点登录**，将鼠标悬停在数据库上将显示 <i class="fas fa-fw fa-sign-in-alt"><span class="visually-hidden"> phpMyAdmin</span></i> 图标。 点击它可以直接登录**phpMyAdmin**。

## 暂停数据库

1. 将鼠标悬停在要暂停的数据库上。
2. 单击数据库名称右侧的<i class="fas fa-fw fa-pause"><span class="visually-hidden">暂停</span></i>图标。
3. 要取消暂停，请单击数据库名称右侧的<i class="fas fa-fw fa-play"><span class="visually-hidden">取消暂停</span></i>图标。

## 删除数据库

1. 将鼠标悬停在要删除的数据库上。
2. 单击数据库名称右侧的<i class="fas fa-fw fa-trash"><span class="visually-hidden">删除</span></i>图标。 数据库用户和数据库都将被删除。
