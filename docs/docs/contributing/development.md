# 为Hestia的发展做出贡献

Hestia 是一个开源项目，我们欢迎社区的贡献。 请阅读[贡献指南](https://github.com/hestiacp/hestiacp/blob/main/CONTRIBUTING.md)以获取更多信息。

Hestia 设计为安装在 Web 服务器上。 要在本地计算机上开发 Hestia，建议使用虚拟机。

::: 警告
开发版本不稳定。 如果您遇到错误，请[通过 GitHub 报告](https://github.com/hestiacp/hestiacp/issues/new/choose) 或[提交 Pull 请求](https://github.com/hestiacp/hestiacp) /拉）。
:::

## 创建用于开发的虚拟机

这些是创建运行 Hestia 进行开发的虚拟机的示例说明。

这些说明使用 [Multipass](https://multipass.run/) 创建虚拟机。 您可以随意调整命令以适应您喜欢的任何虚拟化软件。

::: 警告
有时，本地计算机上的源代码目录与虚拟机中的目录之间的映射可能会丢失，并出现“无法获取远程进程的退出状态”错误。 如果发生这种情况，只需卸载并重新安装即可，例如

```bash
multipass hestia-dev
multipass $HOME/projects/hestiacp hestia-dev:/home/ubuntu/hestiacp
```

:::

1. [安装 Multipass](https://multipass.run/install) 适用于您的操作系统。

2. [克隆分支 Hestia](https://github.com/hestiacp/hestiacp/fork) 并将存储库克隆到本地计算机

   ```bash
   git clone https://github.com/YourUsername/hestiacp.git $HOME/projects
   ```

3. 创建至少具有 2GB 内存和 15GB 磁盘空间的 Ubuntu 虚拟机（如果在 ARM 架构（例如 Apple M1）上运行虚拟机，则至少使用 12GB 内存）

   ```bash
   multipass launch --name hestia-dev --memory 4G --disk 15G --cpus 4
   ```

4. 将克隆的存储库映射到虚拟机的主目录

   ```bash
   multipass mount $HOME/projects/hestiacp hestia-dev:/home/ubuntu/hestiacp
   ```

5. 以 root 身份通过 SSH 连接到虚拟机并安装一些必需的包

   ```bash
   multipass exec hestia-dev -- sudo bash
   sudo apt update && sudo apt install -y jq libjq1
   ```

6. 在虚拟机外部（在新终端中），确保安装了 Node.js 16 或更高版本

   ```bash
   node --version
   ```

7. 安装依赖项并构建主题文件

   ```bash
   npm install
   npm run build
   ```

8. 返回虚拟机终端，导航到`/src`并生成 Hestia 包

   ```bash
   cd src
   ./hst_autocompile.sh --all --noinstall --keepbuild '~localsrc'
   ```

9. 导航到`/install`并使用你需要的组件安装 Hestia（根据您的喜好更新[安装命令]（../introduction/getting-started#list-of-installation-options）请注意此处设置了登录凭据）

   ```bash
   cd ../install
   bash hst-install-ubuntu.sh --hostname demo.hestiacp.com --email admin@example.com --username admin --password Password123 --with-debs /tmp/hestiacp-src/deb/ --interactive no --force
   ```

10. 重新启动虚拟机（并退出 SSH 会话）

   ```bash
   reboot
   ```

11. 查找虚拟机的 IP 地址

   ```bash
   multipass list
   ```

12. 使用默认的 Hestia 端口在浏览器中访问虚拟机的 IP 地址并使用`admin`/`Password123`登录

   （继续执行加载页面时看到的任何 SSL 错误）

   ```bash
   e.g. https://192.168.64.15:8083
   ```

Hestia 现在正在虚拟机中运行。 如果您想更改源代码并在浏览器中测试它们，请继续下一部分。

## 对Hestia进行更改

在虚拟机中设置 Hestia 后，您现在可以使用您选择的编辑器对本地计算机（虚拟机外部）上的 `$HOME/projects/hestiacp` 中的源代码进行更改。

以下是更改 Hestia 的 UI 并在本地进行测试的示例说明。

1. 在本地计算机上的项目根目录中，确保安装了最新的软件包

   ```bash
   npm install
   ```

2. 对稍后可以测试的文件进行更改，然后自定义CS框架。
   例如，在`web/css/src/base.css`中将正文背景颜色更改为红色，然后运行：

   ```bash
   npm run build
   ```

3. 以 root 身份通过 SSH 进入虚拟机并导航至`/src`

   ```bash
   multipass exec hestia-dev -- sudo bash
   cd src
   ```

4. 运行Hestia构建脚本

   ```bash
   ./hst_autocompile.sh --hestia --install '~localsrc'
   ```

5. 在浏览器中重新加载页面以查看更改

::: 信息
每次运行 Hestia 构建脚本时都会创建备份。 如果您经常运行它，它可能会填满虚拟机的磁盘空间。
您可以通过在虚拟机上以 root 用户身份运行`rm -rf /root/hst_backups`来删除备份。
:::

请参阅[贡献指南](https://github.com/hestiacp/hestiacp/blob/main/CONTRIBUTING.md)，了解有关提交代码更改以供审核的更多详细信息。

## 运行自动化测试

我们目前使用 [Bats](https://github.com/bats-core/bats-core) 来运行我们的自动化测试。

### 安装

```bash
#克隆 Hestia 存储库并测试子模块
git clone --recurse-submodules https://github.com/hestiacp/hestiacp

#或者，使用具有最新主分支的现有本地存储库
git submodule update --init --recursive

#安装测试开发版
test/test_helper/bats-core/install.sh /usr/local
```

### 测试运行脚本

::: 危险
不要在实时服务器上运行任何测试脚本。 这可能会导致问题或停机！
:::

```bash
#运行 Hestia 测试脚本
test/test.bats
```
