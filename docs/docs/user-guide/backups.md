# 备份

要管理备份，请导航至**备份<i class="fas fa-fw fa-file-archive"></i>**选项卡。

## 手动创建备份

单击 **<i class="fas fa-fw fa-plus-circle"></i> 创建备份** 按钮。

将显示一个弹出窗口，其中包含以下消息：

**任务已添加到队列中。 当您的备份可供下载时，您将收到一封电子邮件通知。**

## 下载备份

1. 将鼠标悬停在要下载的备份上。
2. 单击备份文件名右侧的<i class="fas fa-fw fa-file-download"><span class="visually-hidden">下载</span></i>图标。

如果备份存储在远程服务器上，文件将下载到服务器，并且当下载可用时您将收到电子邮件通知。

## 恢复备份

1. 将鼠标悬停在要恢复的备份上。
2. 单击备份的文件名或备份文件名右侧的<i class="fas fa-fw fa-undo"><span class="visually-hidden">恢复</span></i>图标。
3. 通过以下方式之一恢复备份：
    1. 您可以通过点击右上角的**<i class="fas fa-fw fa-undo"></i>恢复备份**按钮来恢复整个备份。
    2. 恢复备份的多个部分，方法是选择它们，然后在右上角的 **应用到所选** 菜单中选择 **恢复**，然后单击 <i class="fas fa-fw fa-arrow -right"><span class="visually-hidden">应用</span></i>按钮。
    3. 将鼠标悬停在备份的一部分上并单击<i class="fas fa-fw fa-undo"><span class="visually-hidden">恢复</span></i>图标来恢复备份的一部分 正确的。

## 删除备份

1. 将鼠标悬停在要删除的备份上。
2. 单击备份文件名右侧的<i class="fas fa-fw fa-trash"><span class="visually-hidden">删除</span></i>图标。

## 从备份中排除组件

1. 单击 **<i class="fas fa-fw fa-folder-minus"></i> 备份排除** 按钮。
2. 单击**<i class="fas fa-fw fa-pencil-alt"></i>编辑备份排除**按钮。

### 排除 Web 域

在标有 **Web Domains** 的框中，输入要排除的每个域，每行一个。

要从域中排除特定文件夹，请使用以下语法：

```bash
domain.tld:public_html/wp-content/uploads:public_html/cache
```

这将从该域中排除`public_html/wp-content/uploads/`和`public_html/cache/`。

要排除所有域，请使用“ ` * ` ”。

### 排除邮件域

在标有“**邮件域**”的框中，输入要排除的每个域，每行一个。

要仅排除一个或多个邮件帐户，请使用以下语法：

```bash
domain.tld:info:support
```

这将排除 `info@domain.tld`和 `support@domain.tld`。

要排除所有域，请使用  ` * `

### 排除数据库

在标有 **数据库** 的框中，输入要排除的每个数据库的名称，每行一个。

要排除所有数据库，请使用` * `

### 排除用户目录

在标有 **用户目录** 的框中，输入要排除的每个目录的名称，每行一个。

要排除所有目录，请使用` * `

## 编辑备份数量

要编辑备份数量，请阅读 [软件包](../user-guide/packages) 和 [用户](../user-guide/users) 文档。 您将需要创建或编辑软件包，并将其分配给所需的用户。
