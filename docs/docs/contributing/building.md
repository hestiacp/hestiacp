# 构建包

::: 信息
构建 `hestia-nginx` 或 `hestia-php` 至少需要 2 GB 内存！
:::

以下是有关从“src”运行的构建脚本的更多详细信息：

## 从分支安装 Hestia

以下内容对于测试拉取请求或分叉上的分支很有用。

1. 安装 Node.js [下载](https://nodejs.org/en/download) 或使用 [Node Source APT](https://github.com/nodesource/distributions)

````bash
# 如果你想测试你自己创建的分支，请替换为 https://github.com/username/hestiacp.git
git clone https://github.com/hestiacp/hestiacp.git
cd ./hestiacp/

# 将 main 替换为你要测试的分支
git checkout 主要

cd ./src/

# 编译包
./hst_autocompile.sh --all --noinstall --keepbuild '~localsrc'

cd ../install

bash hst-install-{os}.sh --with-debs /tmp/hestiacp-src/deb/
````

任何选项都可以附加到安装程序命令中。 [查看完整列表](../introduction/getting-started#list-of-installation-options)。

## 仅构建包

````bash
# 仅限赫斯提亚
./hst_autocompile.sh --hestia --noinstall --keepbuild '~localsrc'
````

````bash
# Hestia + hestia-nginx 和 hestia-php
./hst_autocompile.sh --all --noinstall --keepbuild '~localsrc'
````

## 构建并安装包

::: 信息
如果您已经安装了 Hestia，请使用它，以使您的更改生效。
:::

````bash
# 仅限赫斯提亚
./hst_autocompile.sh --hestia --install '~localsrc'
````

````bash
# Hestia + hestia-nginx 和 hestia-php
./hst_autocompile.sh --all --install '~localsrc'
````

## 从 GitHub 更新 Hestia

以下内容对于从 GitHub 提取最新的 staging/beta 更改并编译更改非常有用。

::: 信息
以下方法仅支持构建“hestia”包。 如果您需要构建“hestia-nginx”或“hestia-php”，请使用前面的命令之一。
:::

1. 安装 Node.js [下载](https://nodejs.org/en/download) 或使用 [Node Source APT](https://github.com/nodesource/distributions)

````bash
v-update-sys-hestia-git [USERNAME] [BRANCH]
````

**注意：** 当使用“dpkg”安装软件包时，有时会添加或删除依赖项。 无法预加载依赖项。 如果发生这种情况，您将看到如下错误：

````bash
dpkg: error processing package hestia (–install):
dependency problems - leaving unconfigured
````

要解决此问题，请运行：

```bash
apt install -f
```
