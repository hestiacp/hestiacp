# 开发版和候选版本测试

::: 提示
如果有可用的测试版或候选版本，我们将通过我们的 Discord 服务器或论坛宣布。
:::

在过去的几个月中，我们在发布次要和主要更新时发现了越来越多的问题。 为了防止这种情况发生，我们决定设置一个 beta apt 服务器，这样我们就可以推送更多的定期更新，使我们能够进行比只有 4 或 5 个用户更大的测试规模。

## 在现有安装上激活开发存储库

::: 危险
测试版和候选版本可能仍然包含错误，并且可能会破坏您的服务器。 我们不能保证它会被直接修复！ 在生产服务器或包含重要数据的服务器上进行测试时请小心！
:::

以 root 身份运行以下命令：

```bash
# 收集系统数据
ARCH=$(arch)
case $(arch) in x86_64) ARCH="amd64" ;; aarch64) ARCH="arm64" ;; esac
codename="$(lsb_release -s -c)"
apt="/etc/apt/sources.list.d"

# 将开发版密钥存储库添加到 hestia.list
sed -i 's/^/#/' $apt/hestia.list
echo "deb [arch=$ARCH signed-by=/usr/share/keyrings/hestia-beta-keyring.gpg] https://beta-apt.hestiacp.com/ $codename main" >> $apt/hestia.list
curl -s "https://beta-apt.hestiacp.com/pubkey.gpg" | gpg --dearmor | tee /usr/share/keyrings/hestia-beta-keyring.gpg > /dev/null 2>&1

# 更新至测试版
apt update -y && apt upgrade -y
```

## 从 beta 存储库安装

如果您想从 Beta 服务器安装新的 Hestia。

```bash
# Debian
wget https://beta-apt.hestiacp.com/hst-install-debian.sh
#  Ubuntu
wget https://beta-apt.hestiacp.com/hst-install-ubuntu.sh
```

然后通过 bash hst-install-debian.sh 或 bash hst-install-ubuntu.sh 安装

```bash
# Debian
bash hst-install-debian.sh
#  Ubuntu
bash hst-install-ubuntu.sh
```

## 禁用测试版存储库

编辑`/etc/apt/sources.list.d/hestia.list`，删除`apt.hestiacp.com`前面的`#`，并在`beta-apt.hestiacp.com`之前添加`#`。

完成后，运行“apt-update&&apt-upgrade”以回滚到常规版本。

## 报告错误

如果您遇到错误，请[打开一个问题](https://github.com/hestiacp/hestiacp/issues/new/choose)或[提交拉取请求](https://github.com/hestiacp/hestiacp/pulls). 您也可以在我们的论坛或Discord服务器上报告
