# 更新日志

该项目的所有显着更改都将记录在该文件中。

## [1.8.11] - 服务发布

### 特征

- PHP 8.3 支持 (#4175)
- 添加新模板，默认情况下阻止 Wordpress XMLRPC（仅限 Nignx）（#4168）

### Bug修复

- 对 BACKUP_LA_LIMIT 计算方式的最小改变 (#4162)
- 将 Filegator 更改为 24 小时时钟 (#4168)
- 在恢复部分备份之前确认（#4147）
- 多个错误修复 v-import-cpanel (#4150, #4149 #4144 #4139, #4120, #4121 感谢@vipxr)
- 修复日志标题中小屏幕的问题 (#4126)
- 修复了由于 hestiamail 用户权限更改而导致的一些错误
- 更新了 v-list-sys-user 以修复新 hestiamail 用户的问题
- 使用 -f 代替 --force=yes (#4097)
- 在 Desktop Safari 中延迟提交 (#4137)
- 修复了 v-add-backup-host 中报告无法通过 sftp 连接的错误 (#4119)
- 允许可选的域目录写入权限#4109 @evonet

## [1.8.10] - 服务发布

### Bug修复

- 包括错过的更改
- 包括 <https://github.com/hestiacp/hestiacp/blob/main/install/upgrade/manual/secure_php.sh>

## [1.8.9] - 服务发布

### 安全

- 限制新用户的 PHP-FPM 权限，以防止权限升级到管理员或其他用户 [CVE-xxxx-xxxxx](https://huntr.com/bounties/21125f12-64a0-42a3-b218-26b9945a5bc0/)
- 将 Nginx keepalive_requests 减少到 1000（[Nginx 默认值](https://www.nginx.com/blog/http-2-rapid-reset-attack-impacting-f5-nginx-products/#http2_max_concurrent_streams)）以限制以下风险 [CVE-2023-44487](https://www.cve.org/CVERecord?id=CVE-2023-44487)

### Bug修复

- 修复：在 apache 重新加载期间删除证书 #4053
- 修复：Firehol 黑名单 #4046
- 修复 PHP 错误 + 添加更新 WPCLI + Composer 的选项 #4039
- 更新 v-add-mail-domain #4027 #4026
- 更新 MediaWikiSetup.php #4034
- 修复：对于 v-list-sys-services，Debian Buster 中的 pidof 命令不支持选项 -d #4022
- 更新 main.php humanize_usage_size() #4015
- 修复：防止脚本 v-add-sys-roundcube 在 Roundcube 升级期间冻结 #4018
- 修复了启用 2FA 时的登录问题

### 依赖关系

- 将 Filegator 更新至 7.9.3
- 将 Roundcube 更新至 1.6.4
- 将 Snappymail 更新至 2.29.1

## [1.8.8] - 服务发布

### 安全

- 编辑服务器中 XSS 的安全补丁 (#4013)

### Bug修复

- 改进具有 SSL 支持的 Gitea 模板 (#4012 @asessa)
- 通过 Web UI 批量选项暂停用户时重新启动 Nginx / Apache2 (#4007)
- 修复 v-user-package 中的时间和日期变量 (#4008 #sahsanu)
- 阻止用户创建 ID 为 0 的 DNS 记录 (#3993)
- 将 ipset 文件的最小长度减少到 5。(#3992)
- 将 wp-cli 添加到用户 .bash_aliases 文件中 (#4002)
- 三个 POLICY_USER 指令的默认值 true 为 yes (#3998)
- 更新 v-delete-sys-api-ip (#3994)
- 修复 v-add-sys-quota 和 v-delete-sys-quota (#3991)
- 允许 api 最多使用 13 个参数，而不是 9 个
- 修复了列出管理员用户总体统计数据的问题 (#4001)

### 依赖关系

- 将 Roundcube 更新至 1.6.3
- 将 Blackblaze CLI 更新至 3.10.0
- 更新 Phppgadmin 至 7.4.16

## [1.8.7] - 服务发布

### Bug修复

- 修复 v-update-whitelabel-logo 中的拼写错误

## [1.8.6] - 服务发布

### 特征

- 仅在 localhost 和 TLS 连接上公布身份验证 (#3935)
- 添加了重置自定义日志的功能。 (#3952)

### 安全

- 编辑服务器中 XSS 的安全补丁 (#3946)

### Bug修复

- 修复了 v-generate-ssl-cert 和 IDN 域的问题 (#3942)
- 将source_conf添加到安装程序中
- 修复了白色标签描述（#3952）
- 更新 v-change-mail-domain-sslcert (#3920)
- 改进 v-list-sys-sshd-port 以检查自定义 ssh 端口 (#3922)
- 修复了在新窗口中打开 PHPMyAdmin (#3196)
- 添加换行符 SSL 别名/允许通过下拉菜单清除缓存 (#3917)
- 澄清文档中的 Rclone 说明 (#3948)

## [1.8.5] - 服务发布

### Bug修复

- 修复了添加新包时可能发生的错误 (#3883)
- 修复了 `v-list-sys-interfaces` 中的问题 (#3912)
- 对用户界面代码进行小幅重构 (#3893)
- 改进了 `v-list-web-domain` 的 shell 输出（#3888，感谢#hudokkow）
- 修复了 Ubuntu 系统上 `v-delete-sys-ip` 中的错误 (#3894)
- 将用户角色详细信息添加到 `v-list-users` (#3898)
- 在防火墙 UI 中将“注释”重命名为“链”以更好地反映字段用途 (#3901)
- 更新翻译（#3907）

## [1.8.4] - 服务发布

### Bug修复

- 修复了调用 return_hash 时日志条目会重复的问题 (#3869)
- 修改了安装程序行为，以便仅为必要的服务添加防火墙规则 (#3871)
- 对样式和类别进行细微调整 (#3843)
- 改进了 v-list-sys-themes 的稳健性 (#3842)
- v-update-mail-domain-ssl 的小更新 (#3850)
- 将默认 PHP-FPM 版本更改为 8.2 (#3861)
- v-generate-password-hash 的小更新 (#3874)
- 修复了安装期间无法更新 Cloudflare IP 地址范围时可能出现的问题 (#3876)
- 修复了未安装邮件时的多个 PHP 警告和 500 错误 (#3841，#3877)

### 依赖关系

- 为了简单起见，从项目中删除了打字稿（#3821）
- 删除了 PostCSS 并移至 Lightning CSS (#3820)
- 更新翻译 (#3878)
- 将依赖项 eslint 更新到 v8.46.0 (#3881)
- 将依赖项 esbuild 更新到 v0.18.16 (#3826)
- 将依赖 stylelint 更新到 v15.10.2 (#3829)
- 将依赖关系 Chart.js 更新至 v4.3.2 (#3866)
- 将依赖项 hestiacp/phpquoteshellarg 更新为 v1.0.1 (#3827)
- 将依赖项 @fortawesome/fontawesome-free 固定到 6.4.0 (#3862)
- 更新了翻新配置（#3840）
- 更新了所有非主要依赖项 (#3880)

## [1.8.3] - 服务发布

- [UI] 修复了顶部菜单栏显示错误的用户类型图标的问题 (#3810)
- [UI] 修复了 SSH、API、日志和白标签页面上的后退按钮链接 (#3811)
- [UI] 修复了“未定义 IPset 列表”，即使已定义列表 (#3812)
- [UI] 删除了登录屏幕的动画效果 (#3822)
- [UI] 修复了以“admin”身份登录时未为所有用户返回搜索结果的问题 (#3833)
- [UI]从所有页面加载中删除动画以提高性能（#3836）
- [UI] 将调试模式启用开关移至更合乎逻辑的位置 (#3838)
- [DNS] 确保使用 DNSSEC 正确设置域格式 (#3814)
- [API] 为 API 添加了 update-dns-record 权限 (#3819)

## [1.8.2] - 服务发布

- 向默认代理扩展添加了更多文件 (#3768)
- 增加了移动设备上菜单栏下拉菜单的宽度（#3765）
- 将 HSTS 最大年龄增加到 31536000 (#3762)
- 添加提示“v-update-sys-hestia-git”以安装 NodeJS（如果不存在）（#3779）
- 修复了 Debian 系统上启动时未运行“v-update-sys-ip”的问题
- 修复了使用 Proxmox VE 容器时系统主机名在重新启动时会丢失其 FQDN 格式的问题
- 修复了 `v-generate-ssl-cert` 的问题 (#3783)
- 修复了欢迎电子邮件中缺少端口的问题 (#3784)
- 修复了 `is_mail_new` 函数的问题 (#3785)
- 修复了尝试以管理员身份添加域或数据库时，在解除警告之前会出现“保存”按钮的问题 (#3786)
- 修复了 Ubuntu 上无法安装 MySQL 8 的问题 (#3788)
- 修复了使用 ProFTPD 时的 TLS 连接问题 (#3790)
- 修复了添加 IP 地址时 vlan 或虚拟 NIC 连接无法通过适配器有效性检查的问题 (#3797)
- 修复了控制面板后端中的多个 PHP 500 错误和警告 (#3789)
- 修复了 v-change-dns-domain-ip 和 DNS 集群的问题 (#3803)
- 更新多个快速安装应用程序（#3800 和#3801）
- 更新了语言翻译

## [1.8.1] - 服务发布

- 修复了 Debian 10 无法使用 IP 地址检查的问题
- 修复了通过补丁更新 Exim4 配置不可靠的问题，添加了一些安全检查，并在失败时添加通知。
- 修复了 hestia-nginx 未加载自定义端口的问题

## [1.8.0] - 功能/主要版本

### 注释

- 由于 EOL，不再支持 Ubuntu 18.04 Bionic 请升级到 20.04 或 22.04。
- 由于 Nginx 1.25.1 (#3684、#3704) 中不推荐使用监听指令的 http2 参数以及 (#3692) 中引入的 0-RRT 保护，自定义 nginx 模板需要进行一些更改
- 放弃了对 Rainloop 的支持并由 Snappymail 取代 (#3590)

### 特征

- 添加了对 Debian 12 的支持 (#3661)

- 增强和优化 TLS (#3555 @myrevery)
- 具有重放保护的 TLS 1.3 0-RTT (#3692 @myrevery)
- 在 Exim >= 4.9.5 中添加对 SRS 的支持 (#3197 @henri-hulski)
- 白标支持和重构翻译 (#3441 #3572)
- 改进用户通知 UI (#3709)
- 继续改进 UI（#3700、#3693、#3691、#3685、#3682、#3680、#3672、#3668、#3662、#3659、#3651、#3634、#3629、#3628、# (3619, #3615, #3608, #3606, #3602, #3600, #3598)
- 允许启用/禁用备份挂起用户的选项 (#3696)
- 功能：v-dump-database (#3644)
- 允许用户创建自己的文档错误/框架，并且不要通过更新覆盖它们（#3622）
- 一致的叠加样式 (#3617)
- 集成 SnappyMail (#3590)
- 允许对包名称进行排序 (#3726)
- 为您的模板添加模板 (#3755 @ediazmurillo)

### Bug修复

- 修复：DNS 集群预期返回代码而不是字符串 (#3706)
- 解决 NGINX 的 #3684 处理“http2”指令 (#3704 @myrevery)
- 将 hestiacp.pot 文件直接上传到 Crowdin (#3702)
- 重构添加 ns 按钮 (#3701)
- 从 VestaCP cron.conf 中删除 \r 字符 (#3708 @maunklana)
- 无法编辑密码域 smtp 中继 (#3690)
- 修复：#3687 改进检查别名是否已存在 (#3689)
- 修复了多个接口/IP 地址可用时 v-update-sys-ip 中的错误 (#3688)
- 防止使用空的 ns1 / ns2 (#3683)
- 删除 Web 域时重新加载 Web 服务器。 第3705章
- 修复 sed 安装 sieve (#3679)
- 整洁的开发文档 (#3677)
- 修复 v-delete-sys-filemanager 中的拼写错误 (#3678)
- 改进 DNS SEC 公钥信息显示 (#3676)
- 从 Yarn v3 切换到 npm (#3675)
- 修复#3643：主服务器重建命令上的 SOA 更新 (#3660)
- 修复：当帐户电子邮件为时导入 CPanel不存在 (#3670 #3667)
- 修复：当邮件域和/或 Web 域已存在时导入 CPanel (#3670 #3667)
- 标准化 v-add-user-package 输入 (#3671 #3669)
- deb10 不支持 smtputf8_advertise_hosts (#3652)
- 修复 Gitea 模板 (#3650 @asessa)
- 修复重定向到子文件夹的问题 (#3623)
- 用挂起的模板替换当前的 nginx 模板 (#3641)
- 修复监狱中重复的 phpmyadmin-auth 块的问题 (#3642)
- 修复重建脚本中的错误 (#3639)
- 修复 syshealth 脚本中的错误
- 处理系统 IP/接口的重构和修复 (#3605 @myrevery)
- 修复 #3496 修复 Sieve 和 SMTP 中继的问题 (#3581 @s4069b)
- 添加 phpmyadmin 错误的监狱规则 (#3596)
- 修复 #3599 禁用 SMTPUTF8 (#3603)
- 修复统计行悬停时的内容移动（#3614）
- 修复未选中复选框的问题端口返回“否”(#3616)
- 在发送的电子邮件中对密码进行编码 (#3566)
- 添加对 Mysql 8 的 PHPmyAdmin SSO 支持 (#3539)
- 将 wp-cli 的别名添加到用户的 .bashrc 中并修复错误处理。 (#3569@aosmichenko)
- 简化挂起/取消挂起对话框翻译 (#3565)
- 整洁的通知副本 (#3561)
- 预定义的 Ipset 列表未加载#3552（#3557）
- 对服务器控制台输出的较小 UI 修复 (#3556 @myrevery)
- 修复 #3745 翻译未加载 (#3746)
- 未安装 F2B 时使 IPset 可见 (#3750)
- 修复：#3729 缺少 robots.txt 被重定向到 WP (#3739) / 添加 WordPress 多站点子目录支持 (#3741 @hudokkow)
- 修复全局 SMTP 设置未更新的问题 (#3730)
- 添加 phpbb Nginx 模板 (#3732 #3731 @xchwarze)
- 更新 Nextcloud 模板 (#3725 @Steveorevo)
- 修复更新用户时禁用 DNS 时的 php 错误 (#3726)
- 修复：#3712 无法使用自定义文档根恢复域 (#3726)
- 在 TaskMonitor 上添加两年和三年统计数据 (#3721 @caos30)

### 依赖关系

- 将 hestia-php 更新到 8.2.7
- 将 hestia-nginx 更新到 1.25.1
- 更新快速安装应用程序版本

## [1.7.8] - 服务发布

### Bug修复

- 修复启用调试模式或用户直接访问调试面板模板时调试面板中反映的 XXS。 [CVE-2023-3479](https://nvd.nist.gov/vuln/detail/CVE-2023-3479)

## [1.7.7] - 服务发布

### Bug修复

- 修复#3588：删除问题 DNS 记录 (#3589)
- 整洁的通知副本 (#3561)
- 预定义的 ipset 列表未加载#3552（#3557）

## [1.7.6] - 服务发布

### Bug修复

- 修复了由于 register_shutdown_function 而导致的错误消息“已删除”之前的显示 (#3548 #3547)
- 修复了 humanize_usage_size 中数字格式的问题 (#3546 #3547)
- 修复了 humanize_usage_measure 的舍入问题（#3540 #3541）

## [1.7.5] - 服务发布

### Bug修复

- 修复安装 MariaDB 的问题 (#3543)
- 添加一项检查以防止在 Debian 上安装 ARM64 和 Mysql8 (#3543)

## [1.7.4] - 服务发布

### 特征

- 在发布时构建 JS/CSS 主题 (#3525)
- 重构 jQuery

### Bug修复

- 删除 Font Awesome“品牌”用法 (#3535)
- 使 uft8mb4 成为数据库的默认字符集 (#3534)
- 删除 SSO url 中的多余斜杠 (#3533)
- 改进快速安装应用程序密码输入（#3530）
- 如果 OpenSSL 服务器在验证 SSL 证书之前已经运行，则终止该服务器 (#3505)
- 改进重定向行为 (#3503)
- 修复：CP 面板模板的 PMA SSO (#3493)
- 修复：sftp 备份中的错误 (#3489)
- 改进快速安装应用程序密码输入（#3530）
- 重构登录页面上的点击使用情况 (#3526)
- 重构添加/编辑防火墙规则 JS (#3522)
- 构建 Alpine.js 包 (#3521)
- 改进图表 JS (#3519)
- 确认对话框操作时显示微调器 (#3517)
- 重构编辑 Web JS/删除 jQuery (#3513)
- 重构添加/编辑数据库 JS (#3511)
- 用户界面更新（#3510）
- 重构 JS (#3508)
- 修复 #3318 删除：爆裂警告 MariaDB (#3465)
- 修复：3514 修复 UI 与真实值不匹配的问题 (#3515)
- 重构表单提交 JS (#3502)
- 重构 JS (#3500)
- 重构无限输入 JS (#3495)
- 整洁的 JS (#3492)
- IPV6 兼容防止 CSRF (#3491)
- 重写统计 UI 移动优先 (#3490)
- 重构 JS (#3488)
- 将配额信息添加到用户列表中 (#3487)
- 次要的 UI 更新 (#3485)
- 动态加载 Chart.js 包 (#3480)
- 重构 JS 以使用 ES 模块 (#3476)

## [1.7.3] - 服务发布

### 特征

- 在 Chart.js 中重新实现 RRD 图表 (#3452)
- 添加 JS/CSS 构建脚本 (#3471)

### 依赖关系

- 将 hestia-php 更新到 8.2.5
- 将 hestia-nginx 更新到 1.23.4

### Bug修复

- 修复：命名命令警告 (#3447 @neto737)
- 修复：在安装过程中包含 Cloudflare IPS (#3449 #3448)
- 修复：upgrade_phppgadmin 中的错误导致文件夹不存在时无法创建 (#3450)
- 向 php-fpm 模板添加警告 (#3450)
- Exim：从不为经过身份验证的用户显示 HELO (#3462 @myvesta)
- 创建手动备份时弹出通知上的误导性标题“错误”（＃3460＃3461）
- 修复：不要添加尾随 . 在 DNSKEY #3458 上
- 修复了某些情况下移动设备上的工具栏间距，例如 备份页面 (#3460)
- 修复：用户无法创建新的 DNS 域 (#3451)
- 修复：包含 html 的错误消息编码两次 (#3473)
- 修复按钮宽度回归 (#3474)
- 删除模态背景的不透明度 (#3460)
- 重构添加/删除名称服务器 JavaScript (#3468)
- 重构“无限”输入（#3464）
- 重构密码强度 JS (#3459)

## [1.7.2] - 服务发布

### 笔记

- HestiaCP 1.7.2 修复了由于异步请求的实施而从 Let's Encrypt 下载证书的问题，该问题将于 2023 年 4 月 10 日上线。请在此日期之前更新您的服务器，以确保与 Let's Encrypt 的兼容性。

### Bug修复

- 修复了默认 php 版本更改后的 php 问题 (#3145 #3414)
- 修复了导入添加域 v-import-cpanel (#3242 @adion-gorani)
- 修复了 DNSSSEC 检查 DNSEC 是否可用的问题 (#3430)
- 修复了 v-add-web-domain-redirection 的问题 (#3438 #3440)
- 删除域上的前导和尾随空格 (#3439 #3440)
- 修复了 v-backup-users 中的 domain.com:/public_html 问题 (#3434)
- 自定义网络邮件客户端的修复和问题 (#3419 #3420)
- 优化：焦点样式 (#3432)
- 用 vanilla JS 替换 jQuery UI 选项卡 (#3413)
- 减少动画样式的数量 (#3418)
- 次要 UI 更新 (#3425)
- 修复了禁用后 v-suspend-dns-record 仍在加载的问题 (#3441 @setiseta)
- 用 <dialog> 替换 jQuery UI 对话框 (#3401)
- 修复了登录页面上未找到 SSL + php 错误的问题。 (#3404)

## [1.7.1] - 服务发布

### Bug修复

- 修复了 Apache2 中通配符推翻 webmail.domain.com 配置的问题 (#3400 #1631)
- 删除删除按钮编辑用户页面（#3997）
- 修复了序列号不增加的问题 (#3396)
- 修复了新的 hestia-zone 同步和 NAT 后面的服务器或多个 IP 的问题 (#3388 #3396)
- 删除不支持 DNSSEC 时启用 DNSSEC 的选项 (#3372 #3396)
- 修复带有长单词的区域设置上的工具栏项目 (#3380 #3395)
- 只计算循环例程中的 \*.tar 文件 (#3393 #3385)
- 修复了损坏的upgrade_mariadb.sh (#3391 @myrevery)
- 改进 add_firewall_ipset.php (#3390 @myrevery)
- 更新 IPset blacklist.sh 的路径更改 (#3389 @myrevery)
- 改进升级脚本 Cloudflare ips (#3388 @myrevery)
- 更新支持的消息 hst-install.sh (#3377 @shizualand)
- 修复了将自己的 ssl 认证添加到网站配置的问题 (#3374 #3371)
- 修复了 javascript 逻辑编辑邮件域 (#3373)
- 向登录表单添加必需的属性 (#3376)

## [1.7.0] - 功能/主要版本

### 笔记

- Debian 9 (Stretch) 不再受支持，因为它已达到生命周期结束状态。
- 此版本中添加了基本的移动支持。 这是早期阶段，我们非常感谢您的反馈和任何错误报告，以进一步改善移动体验

### 特征

- 添加了对移动设备的基本支持（#3166、#3141、#3142、#3157、#3155、#3120 等等）
- 添加了对 DNS 域的 DNSSEC 支持 (#2938)
- 添加了对 MySQL 8 的支持（对于新安装）(#xxxx @xxxxx)
- 在包定义中添加了对 exim 速率限制的支持 (#2920)
- 添加了对 SFTP 备份的 ssh 密钥的支持 (#2906)
- 添加了 Rclone 支持备份到 AWS、Cloudfare 和 [+40 个其他存储系统](https://rclone.org/overview/) (#2928)
- 添加了对导入 Cpanel 备份的支持（#3238、#3232 @skamasle）
- 添加了对备份排除中的文件夹通配符的支持 (#2338 @youradds)
- 为 Mautic 添加了 Nginx 模板 (#3192 3188 @youradds)
- 添加了作曲家的别名（#3070）
- 更新了 PhpPgAdmin 并支持 PostgreSQL 15 (<https://github.com/hestiacp/phppgadmin>)
- 将 MariaDB 升级到 10.11 (#3305)
- 添加 Flarum 快速安装程序（#3342 和 #3298 @Steveorevo）
- 默认为 Mysql 启用 UTF8MB4 (#3337 #1882)
- 每次更新时更新 Cloudflare IP 地址 (#3338 #2575)

### Bug修复

- 使 .yaml 文件在文件管理器中可编辑（#3200 @BelleNottelling）
- 修复了搜索不支持用户模拟的问题。 （#3208#3199）
- 阻止用户重命名 /home/user/web/ 中的目录 (#3211)
- 允许用户帐户“名称”字段使用特殊字符 (#3210)
- 防止用户名包含特殊字符 (#3220 #3213)
- 增加 DKIM 长度 (#3218)
- 改进密码表 CSS (#3221)
- 改进重启行为 v-update-letsencrypt-ssl (#3231)
- 修复升级时应用补丁的顺序（#3239）
- 改进 Roundcube 和 Filegator 的升级行为 (#3237 #3236)
- 允许 <ClientName@domain.com> 通过 Dovecot/Email 登录 (#3024)
- 当无法通过 API 连接时返回正确的错误代码 (#3235 #3169)
- 同步 $BIN 和 $HESTIA/BIN (#2185 @Steveorevo)
- 阻止使用无限备份（#3181）
- 将路径 /var/run/ 更新为 /run (#3159)
- 更新各种快速安装应用程序上的 PHP 版本 (#3167 #3149 @dadangnh)
- 将 Media Wiki 版本更新至 1.39.1 (#3168 @kizule)
- 用 libcurl 替换自定义 HTTPS 套接字代码 (#3160)
- 添加配置以避免错误时重新启动守护进程 (#3183 @joeakun)
- 修复了默认模板和快应用安装程序的问题 #3133
- 概括密码重置说明。 第3112章
- 允许.tpl文件es 可在文件管理器中编辑 (#3148 @neto737)
- 修复了未为具有两层 TLD 的域（例如 .co.uk）创建域别名的问题 (#3030)
- 修复/同步现有域的问题 (#3028)
- 修复了无法创建 tmp 目录的问题 (#3019)
- 修复了 Fail2Ban 中 mysqld-iptables 的问题 (#3025)
- 修复了 Logrotate 和 Awstats 的问题 (#3297)
- 添加了 Google Public DNS 作为 nginx 配置的辅助解析器
- 修复了 Proftpd 和被动模式外部 IP 的问题 (#3266)
- 改进 v-change-sys-port 中的 IPv6 处理 (#3276 @asmcc)
- 在 Ubuntu 22.04 上为 hestia-php 设置正确的冲突
- 修复了错误删除 $domain.\* 而不是 $domain.pem 的问题 (#3221)
- 修复了域重定向和 idn2 域的问题 (#3323 #3325)
- 修复了 Dokuwiki 中由于更改存储库所有者而导致的问题 (#3327)
- 修复了 B2 和更改访问密钥的问题
- 修复了通过快速安装程序安装 Drupal 的问题 (#3353 #3352)
- 修复了默认状态 jQuery UI 模式的问题 (#3344)
- 修复了使用第一个字符包含 - 或 - 的密码登录的问题 (#3365 #3354)
- 添加禁用 ip 检查的选项 (#3365)
- 将 Apache2 / PHP-FPM 设置的 default.tpl 中的 sdocroot 替换为 docroot (#3360)

### 依赖关系

- 将 hestia-nginx 更新至 1.23.3
- 将 hestia-php 更新至 8.2.4
- 将 OpenSSL 更新至 3.1.0
- 将 Roundcube 更新至 1.6.1
- 将 Filegator 更新至 7.9.2
- 更新 phpMyAdmin 至 5.2.21
- 将 phpPgAdmin 更新至 7.3.14-hestiacp
- 将 MediaWiki 更新至 1.39.2
- 将 Prestashop 更新至 8.0.1
- 将 TwoFactorAuth 更新至 2.0.0

## [1.6.14] - 服务发布

＃＃ Bug修复

- 改进防火墙规则清理#3135 @myrevery
- 恢复了对 v-add-web-php 的更改，因为 php8.2-imagick 现已可用
- 修复了编辑服务器时编辑时区的问题 (#3127)
- 修复了安装过程中主机名的问题
- 修复了 WordPress 安装程序无法正常工作的问题 (#3129)

### 依赖关系

- 将 MediaWiki 更新至 1.39.0

## [1.6.13] - 服务发布

### Bug修复

- 修复 php8.2-imagick 不可用的问题
- 修复了 Letsnecrypt 和未启用邮件功能的问题 (#2930 #2931)

## [1.6.12] - 服务发布

### 特征

- 添加对 PHP 8.2 的支持

### Bug修复

- 修复了 Debian / Ubuntu 中不存在的 Europe/Kyiv 导致保存问题的问题 (#3031 #2971)
- 修复了当用户无法创建临时文件夹或空间不足时 v-backup-user 循环的问题 (#2923 #3019)
- 修复了通过 api 重新启动的问题 (#1236 #30230)
- 修复了 \*.co.uk 和类似域不创建 www 别名的问题（#1750 和 #3030）
- 修复了启用 mysqld-iptables 的问题 (#3035 @Krzysiek86 @neto737)
- 在 bash_aliases 中添加作曲家的别名 (#3070 @madito)
- 修复安装程序中多个 ip 和主机名的问题 (#3068)
- 修复了 Nginx + Apache2 设置和清除代理缓存的问题 (#3060)
- 更新 WordPress 以避免缓存 WordPress Rest API (#3069 @niktest)
- 修复了防火墙和 IPset 表少于 10 条记录的问题 (#3110 @myrevery)
- 在删除数据库主机上删除rrd数据库
- 修复了用户无法更改数据库用户的问题#3051
- 修复了取消挂起数据库用户权限（远程）的问题#3011#3046
- 修复了 v-add-domain 和软件包不允许邮件/网络或 DNS 域的问题

### 依赖关系

- 将 PHPmailer 更新至 6.7.1

## [1.6.11] - 服务发布

### 重要的

v-update-sys-hestia 中的错误导致自动更新无法工作。 请运行：`apt update && apt update`

### 安全

- 修复会话超时和 filemnanger 问题 (#3004)

### Bug修复

- 修复了 HestiaCP 自动更新脚本的问题 (#2967)
- 修复了在 Firefox 中下载 ssl 证书的问题 (#2979)
- 解决 IDN 域和重定向问题 (#2988)
- 更新英国的 Ipverse 网址 (#2962)
- 修复了查看系统日志时图标消失的问题
- 修复了编辑邮件帐户上无限配额按钮的问题

### 依赖关系

- 将 Rainloop 更新至 1.17.0 (#2957)
- 将 Zlib 更新至 1.2.13
- 将 hestia-nginx 更新到 1.23.2
- 将 hestia-php 更新到 8.1.12
- 将 OpenSSL 更新至 3.0.7
- 将 Filegator 更新至 7.8.7

## [1.6.10] - 服务发布

### 安全

- 验证密码后删除临时文件 (#2958)

### 依赖关系

- 将 Filegator 更新至 7.8.3
- 将 PHPmailer 更新至 6.6.5

## [1.6.9] - 服务发布

- 修复了在非英语语言环境中安装 Wordpress 时的问题 (#2788 #2818)
- 在清除快速 cgi 缓存时重新加载 Nginx (#2925)
- 更新名称允许的最大字符数 (#2924)
- 修复了 Lets Ecypt for hostname 的一些小问题 (#2922)
- 修复了快速安装程序的一些问题 (#2921)
- 修复了 v-change-web-domain-name 的问题
- 更新sync-dns-cluster角色以运行v-delete-dns-domain (#2943)
- 修复了与运行 FreeBSD 的 sftp 服务器连接的问题 (#2950 @gdarko)
- 添加对库尔德 Sorani 的支持 (#2945 @qezwan)
- 语法上的小改进v-add-remote-dns-host (#2951)
- 检查电子邮件对于 PHPMailer 是否有效 (#2944)

### 依赖关系

- 将 Dokuwiki 更新至 stable_2022-07-31a
- 将 Opencart 更新至 4.0.1.11
- 将 Prestashop 更新至 1.7.8.7
- 将登录页面上的 Jquery 更新至 3.6.1 (#2932 @4183r)
- 将 hestia-php 更新到 8.1.11

## [1.6.8] - 服务发布

### 特征

- 更新默认的 php 设置 (#2849 #2635)

### 安全

- 修复 is_hestia_package 中的问题 (#2889)

### Bug修复

- 当未提供版本时，强制将 Composer 更新为 v2 而不是 v1 (#2839 #2777)
- 修复了 v-change-web-domain-owner 和仅邮件域的问题（#2840、#2841）
- 灰显 phpmyadmin 按钮 + 添加 docs.hestiacp.com 链接以获取支持 (#2843)
- 阻止在 @ 或根记录 DNS 域上使用 CNAME 记录（#2838、#2842）
- 代码清理并删除未使用的测试和模板（#2829 和#2831）
- 修复了用户创建新邮件帐户时未发送密码的问题 (#2819 #2815)
- 修复了 Proxmox LXC 和主机名的问题 (#2819 #2048)
- 改进发送给用户的新电子邮件帐户电子邮件 (#2819 #1417)
- 改进缓冲区 nginx.conf (#2796)
- 改进 Letsencrypt 错误消息 (#1804 #2854)
- 修复了错误日志登录尝试失败的问题 (#2853)
- 修复了在编辑服务器中保存 UTC 时区的问题 (#2851 #2853)
- 修复了 sshd 未运行但在极少数情况下仍然出现的问题 (#2850 @manuelserol)
- 当“主”域属于其他用户时，改进错误消息“域已存在”(#2848 #2771)
- 修复了删除邮件域 SSL 时 v-delete-letsencrypt 不起作用的问题 (#2878)
- 修复了在 b2.conf 中存储 B2 密钥的问题 (#2843)
- 使用示例更新 Jail.local 以添加忽略 ip (#2856)
- 将 use_temp_path 添加为 no 以稍微加速缓存 (#2855)
- 修复 php 小错误 (#2863 #2857 @YacineSahli)
- 修复了多个服务器上的 API 和 DNS 集群问题，其中用户名/密码和哈希值混合在一起 (#2888)
- 添加使用自定义 javascript 代码的选项 (#2747)
- 将“v-rebuild-dns-domains”添加到sync-dns-cluster选项
- 修复了 Yescript 和 api 的问题 (#2899)
- 为 Roundcube 添加 logrotate 配置 (#2868 #2904)
- 修复了由 /web/inc/mail-wrapper.php 引起的 /tmp/ 文件夹中的会话文件问题 (#2904)
- 修复了 v-restore-user 在恢复之前不删除旧数据库导致新表仍然存在的问题 (#2911 #2909)
- 修复了删除邮件帐户不会删除该电子邮件帐户的速率限制的问题（#2905 #2903）

### 增强功能

- 清理/减小图像、图标、javascript、css 和 html 的大小（#2879、#2871、#2872、#2873、#2884、#2883、#2879 @AlecRust）

### 依赖关系

- 将 hestia-nginx 更新到 1.23.1
- 将 hestia-php 更新至 8.1.9
- 将 animate.js 更新到 3.0.2 (#2879)
- 将 normalize.css 更新为 3.0.3 (#2875)
- 将 jQuery 更新到 3.6.1 (#2885)
- 将 MediaWiki 更新至 1.38.2
- 将 PHPmailer 更新至 6.6.4
- 将 Blackblaze CLI 更新至 3.5.0

## [1.6.7] - 服务发布

### Bug修复

- 修复了升级脚本 Roundcube 导致新安装升级出现问题的问题
- 修复了 DNS 模板的错误 #2827
- 更新 v-update-sys-hestia-git

## [1.6.6] - 服务发布

### Bug修复

- 使用 CNAME 更新 ftp、www 和 webmail 的 DNS 模板 (#2808)
- 修复名称服务器 A 记录验证错误 (#2807)
- 修复了重命名域和配置文件未正确删除的问题 (#2803)
- 单击保存按钮后添加加载指示器 (#2740)
- 改进邮件包装器中的主机名检测（#2805 @clarkchentw）

### 安全

- 修复了 v-add-web-domain-redirect 中的漏洞 (CVE-2022-2636)
- 修复了 Ubuntu 中的一个漏洞，该漏洞可能导致管理员权限升级为 root 用户 (CVE-2022-2626)

### 依赖关系

- 将 Roundcube 更新至 1.6.0
- 将 Dokuwiki 更新为“2022-07-31”Igor (#2811)

## [1.6.5] - 服务发布

### Bug修复

- 添加缺失的翻译字符串 (#2778 @myrevery)
- 添加检查 v-change-web-domain-docroot 中是否存在文件夹 (#2778)

### 安全

- 改进随机字节生成器（#2774）
- 不允许直接从网络浏览器调用 /inc/2fa/secret.php (#2784 @mayappear)
- 改进 CSRF 起源检查绕过 (#2785 @mayappear)
- 修复 Dokuwiki 快速安装应用程序 @redstarp2 中的漏洞 (CVE-2022-2550)
- 修复了重启fail2ban服务时未保存自定义端口的问题，导致Hestia登录屏幕容易受到暴力破解

### 依赖关系

- 将 Filegator 更新至 7.8.2

## [1.6.4] - 服务发布

### Bug修复

- 修复了下载日志文件的问题 ()
- 修复了安装快速安装程序的问题（#2762、#2760、@divinity76）
- 修复了使用 v-update-sys-ip 后 Apache Access / Awstats 记录 IP 的问题 (#2759 @adion-gorani)

## [1.6.3] - 服务发布

### 特征

- 添加对邮件密码 bcrypt 的额外支持 (#2752 @divinity76)

### 增强功能

- 简化重置表单电子邮件中的 md5crypt (#2751 @divinity76)
- 使用安全 RNG 生成密码 (#2726)
- 添加 twig 支持文件管理器（#2714，@anvme）

### Bug修复

- 使固定修复了 v-update-letsencrypt 后重新启动 Apache2 和 Nginx 的问题（#2748、#2563、#2744、#2677）
- 防止快速安装应用程序中的横向路径 (#2742)
- 避免内存不足提供大型日志文件（#2741，#2736，@divinity76
- 改进password_valid中的密码加载（#2739）
- 使用安全 RNG 生成密码 (#2726)
- 使用整个字母表作为随机字符串（#2735 @Shadowfied）
- 不要在 Exim 中为 Gmail / Google 主机名使用hosts_try_fastopen
- 添加检查 Sieve 是否已安装 (#2719 #manuelserol)
- 允许在快速安装程序应用程序中选择 PHP 模板（#2713、#2711、#2690）
- 对翻译字符串的小改动 (#2700 @V4M0N0S)
- UI 中电子邮件地址空白的速率限制（在限制中正确保存）（#2710、#2707）
- 修复了“设置”网站中的一个错误，该错误始终导致网站在保存时重建（#2705、#2710）
- 修复了博客中的错误，该错误导致会话被错误地重置为管理员用户 (#2710)
- 防止 v-add-web-php 用于非 fpm 安装 (#2753)
- 更新翻译（#2750）
- 创建文件管理器 ssh 密钥时 Chmod o+x .ssh 文件夹 (#2755)

### 依赖关系

- 将 hestia-php 更新到 8.1.8
  - 更新 hestia-php 的禁用函数列表 php.ini (#2746, #2741)

## [1.6.2] - 服务发布

- 修复了 Exim4 中的速率限制问题并使其更加防弹 (#2703)
- 修复了 Exim4 中系统过滤器未正确加载到 Exim 4.94 和从 1.5.x 升级的问题

## [1.6.1] - 服务发布

### Bug修复

- 修复了速率限制和别名域的问题（#2676、#2666）
- 修复了拒绝垃圾邮件选项的问题（#2687、#2864）
- 修复了启用 sieve 时安装程序中的问题（#2675、#2668）
- 修复了开发模式下文件管理器的问题 (#2682 #2644)
- 修复了模板中的多个小问题（#2659 @ledoktre、#2680、#2671、#2679、#2670、#2681、#2699）
- 修复了 DNS 解析失败时添加第二次检查的问题 (#2678)
- 修复了 v-change-sys-hostname 不更新主机文件的问题 (#2688 #2683)
- 修复了 IDN 转换在新服务器安装上不起作用的问题 (#2692 @wojsmol)

### 增强功能

- 改进 php-fpm 的重启行为
- 改进更新过程以使其更快。
- 删除了过时的/从未使用过的测试脚本（#2685）

### 依赖关系

- 将 hestia-nginx 更新到 1.23.0
- 将 PHPmailer 更新至 6.6.3
- 将 Roundcube 更新至 1.5.3

## [1.6.0] - 主要版本（功能/质量更新）

### 重要笔记

- 添加了对 Ubuntu 22.04 Jammy 的支持。 如果您计划将服务器从 Ubuntu 20.04 或 18.04 升级到 Ubuntu 22.04，请仔细阅读说明！
- 已发现 Ubuntu 和 Netplan 以及其他 IP 地址的问题，如果您的设置出现这种情况，请检查 Netplan 配置是否正确。
- 由于已知 Rainloop [CVE-2022-29360](https://blog.sonarsource.com/rainloop-emails-at-risk-due-to-code-flaw/) 的安全问题并且缺乏更新 我们计划用 [Snappymail](https://github.com/the-djmaze/snappymail) 更新/替换 Rainloop。 Snappymail 的发布需要多么小的改变。 所需的更改已完成，但我们正在等待 2.16.4 的最终版本
- 添加了对 Yescrypt 和 ARGON2ID 的支持，用于存储用户/电子邮件帐户密码的密码。 如果您在日志记录方面遇到任何问题（导入备份后），请更改用户/电子邮件帐户密码，它将解决任何问题。

### 已弃用

- 不再支持新安装的 Debian 9 (#2537)
- 安装时放弃了对 Ubuntu 18.04 上 RSSH 的支持 (#2537)
- Dovecot 不再支持 TLS1.1 及更早版本（#2012 和 #2538）

### 特征

- 添加了对 Ubuntu 22.04 Jammy 的支持 (#2537 #2489)
- 通过 UI 添加了对电子邮件帐户的 Exim 速率限制的支持（#2225 和 #2523 @madito）
- 添加了在达到特定阈值时删除垃圾邮件的支持（#2206 和#2200 @madito）
- 添加了对将邮件发送到未经身份验证的 SMTP 中继的支持 (#2441 @clarkchentw)
- 将 Debian 10 和 Ubuntu 20.04 及更高版本的默认 MD5 编码替换为 ARGON2ID (#2421 @stsimb)
- 添加了对 Yescrypt 的支持 (#2235 / #2499)
- 由于兼容性问题 Jammy 将后端升级到 PHP8.1 (#2515)
- 引入新的 API，允许用户通过 API 使用某些命令（#2535 和 #1333）
- 允许“清除”缓存按钮在名为“cacheing-your-template-name”的模板上可见（#2526 #2530）
- 添加钩子到 hestia-nginx 和 hestia-php (#2440)
- 更新 DNS 集群以支持新的 API 系统 (#2587)

### Bug修复

- 修复了使用 --interactive no 时 --hostname 和 --email 未验证的问题 (#2532 #2531)
- 修复了 MariaDB 10.7 是否正在运行的检测问题 (#2536 @gOOvER)
- 修复了以标准用户身份下载备份的问题 (#2524 #2525)
- 删除重复的软件包安装程序 (#2520 @rfzh1996)
- 修复了“不允许用户登录”复选框与真实设置同步的问题 (#2507 #2513)
- 修复了删除暂停用户不会减少暂停用户计数器的问题 (#2504 #2531)
- 修正了一个问题启用重定向的域无法“请求”加密 ssl (#2514 #2176)
- 在基于 ARM64 的服务器上使用 Blackblaze 时添加通知 (#2394 @zedpr0)
- 添加 rsyslog 作为依赖项 (#2505)
- 修复了当用户导入备份时默认情况下不会创建 Let's Encrypt cronjob 的问题。 (#2498@NickCoolii)
- 在备份列表中添加缺少的翻译转换 (#2501)
- 更新 v-add-web-domain-backend 中的示例 (#2500gingbeardman)
- 更新 v-add-letsencrypt-domain 中的示例 (#2442)
- 通过加载 /etc/hestiacp/hestia.conf 修复了 configure-server-smtp.sh 中的问题 (#2488)
- 更新 nginx.conf 中的 Cloudflare ip (#2542 @clarkchentw)
- 删除 Ubuntu 安装程序中的重复代码 (#2542 @clarkchentw)
- 修复了 Nginx + Apache2 邮件“禁用”模板中的问题。 导致用户无法请求有效的 ssl 证书 (#2550 #2549)
- 修复了“拒绝垃圾邮件”选项不起作用的问题 (#2551 #2545)
- 修复了编辑/添加 DNS 记录的问题（#2546、#2547、#2548 @DunoCZ）
- 修复了 TXT 记录长度超过 255 个字符的问题 (#2559)
- 修复了 wp-cli 权限被拒绝的问题，并允许 wp-cli 在 v-run-cmd 命令中运行（#2562 和 #2565）
- 修复了 apt-get install 输出未写入安装日志的问题 (#2585)
- 修复了改进的 WordPress 快速安装应用程序的多个问题 (#2583)
- 上游包的更改导致 phpMyAdmin 单点登录功能中断 (#2591)
- 修复了 DNS 集群和新 API 的问题 (#2587)
- 修复了 Apache2 设置的 PHPpgAdmin 配置文件未重命名为 .inc 的问题 (#2592)
- Ubuntu 22.04 启动时启动 Fail2ban (#2596 #2594)
- 修复了重复配置值的问题（#2640 @Kujoe 和#2605 #2610）
- 修复了网络邮件客户端更改密码功能的问题
- 修复了一般快速安装应用程序的多个问题（#2444、#1092、#2638）
- 修复了内存使用图和非英语语言环境的问题 (#2643 #2540)
- 修复了 ftp 备份下载路径不正确的问题 (#2636 @cloudyhostcom)
- 在 v-run-cli-cmd 中添加 php8.1 (#2630 @gOOvER)
- 修复了通配符和 Letsencrypt 的多个问题（#2627、#2626、#2624、#2623）
- 修复了 v-change-domain-owner 中的多个问题（#2618、#2617、#1864）
- 修复了 MariadDB 10.8 检测的问题 (#2616)
- 修复了 netplan 和其他 IP 地址的问题 (#2612)
- 从 Ubuntu 22.04 安装中删除了 MariaDB 存储库
- 如果安装 sieve 时缺少 Roundcube，请勿安装 Roundcube 依赖项。
- 删除 v-add-web-domain-ssl 中的重复代码

### 依赖关系

- 将 hestia-nginx 更新到 1.22.0
  - 将 OpenSSL 更新至 3.0.3
  - 将 zlib 更新至 1.2.12
  - 将 PCRE 更新至 10.40
- 将 hestia-php 更新到 8.1.7
- 将 phpMyAdmin 更新至 5.2.0 (<https://www.phpmyadmin.net/files/5.2.0/>)
- 将 Filegator 更新至 7.8.1
- 将 PHPmailer 更新至 6.6.2
- 更新作曲家依赖项

## [1.5.15] - 服务发布

### Bug修复

- 修复了通配符 DNS 记录的问题

### 依赖关系

- 将 phpMyAdmin 更新至 5.1.4 (<https://www.phpmyadmin.net/files/5.1.4/>) (#2529)

## [1.5.14] - 服务发布

### Bug修复

- 修复了使用 ipv6 登录的问题 (#2564)
- 修复了包含 .dns 记录的问题。 (#2559)

## [1.5.13] - 服务发布

### Bug修复

- 修复了通过 GUI 添加/更改 DNS 记录的问题。 (#2557)

## [1.5.12] - 服务发布

### Bug修复

- 修复了 Sed 的漏洞 [CVE-2022-1509](https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2022-1509)
- 删除使会话无效的本地主机异常 [SSD 披露](https://ssd-disclosure.com/ssd-advisory-vestacp-multiple-vulnerability/)

## [1.5.11] - 服务发布

### Bug修复

- 修复了 Hestia 端口更改未更新fail2ban 链的问题 (#2465)
- 修复了 /var/log/roundcube 的权限问题 (#2466)
- 修复了 UI 中的多个问题 (#2464)
- 允许 v-change-user-template 更新后端模板 (#2475)
- 更新作曲家依赖项 (#2458 #2459)
- 修复了“编辑”服务器页面中的 XSS 漏洞。 (#2471) [CVE-2022-0986](https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2022-0986)
- 修复了缓存模板和内部重定向的问题 (#2482)

## [1.5.10] - 服务发布

### Bug修复

- 修复了 Web 邮件客户端选项未显示在 Web UI 中的问题 (#2445)
- 修复了用户无法创建备份的问题。 （＃2448 /＃2449）
- 修复了由于 mod-php 服务器上的 PHP 版本检查不正确而导致保存服务器设置可能失败的问题 (#2451)
- 修复了执行 HestiaCP v1.5.9 全新安装时 MariaDB 安装中断的问题 (#2452 | 2446)
- 修复了最近发现的 XSS 漏洞 (#2453) [CVE-2022-0838](https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2022-0838)

## [1.5.9] - 服务发布

### Bug修复

- 修复了网页用户界面中的多个 XSS 漏洞。 [CVE-2022-0752](https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2022-0752) / [CVE-2022-0753](https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2022-0753)- 修复了 mariadb.sys 用户在 MariaDB 10.6.x 安装上无法正常工作的问题 #2427
- 将 ipverse.net url 更改为 Github #2429 和论坛上托管的新格式
- 允许在domain.com上使用PTR

### 依赖关系

- 将 PHPMailer 更新至 6.6.0 (<https://github.com/PHPMailer/PHPMailer/releases/tag/v6.6.0>)
- 将 Filegator 更新至 7.7.2 (<https://github.com/filegator/filegator/releases/tag/v7.7.2>)

## [1.5.8] - 服务发布

### 特征

- 没有引入新功能

### Bug修复

- 修复了无法为其他 FTP 帐户正确启用 SFTP 监狱的问题 #2403
- 修复了安装程序中的“按任意键继续”提示仅响应 Enter 键的问题 #2398
- 修复了列表排序顺序首选项变量未正确保存的问题#2391
- 修复了邮件帐户设置信息对话框中行为不一致的问题 #2392
- 修复了 /root/ 中的 .gnupg 文件夹权限设置错误的问题。
- 修复了用户在访问 /reset/ 端点时被重定向到登录页面的问题 #2401
- 修复了删除 sftp 监狱没有恢复该用户权限的问题。 第2143章
- 修复了“REDIRECT”变量未正确清除导致其他站点在 v-update-letsencrypt-ssl 后重定向到该域的问题
- 将新安装的存储库 URL MariaDB 更改为 <https://wdlm.mariadb.com/repo/mariadb-server>

### 依赖关系

- 将 phpMyAdmin 更新至 5.1.3 (<https://github.com/phpmyadmin/phpmyadmin/releases/tag/RELEASE_5_1_3>)

## [1.5.7] - 服务发布

### Bug修复

- 修复了 apt 更新和公钥丢失的问题

如果您必须遵循错误

```bash
由于公钥不可用，无法验证以下签名：A189E93654F0B0E5
```

请按照以下说明操作

````bash
rm /usr/share/keyrings/hestia-keyring.gpg
mkdir /root/.gnupg/
gpg --no-default-keyring --keyring /usr/share/keyrings/hestia-keyring.gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys A189E93654F0B0E5
````

之后运行 apt update && apt Upgrade

## [1.5.6] - 服务发布

### Bug修复

- 修复了安装程序的问题。 system.pkg 不允许使用 Web 别名 #2381
- 修复了升级脚本导致命令执行的问题 (<https://forum.hestiacp.com/t/upgrading-to-1-5-5-error-line/5449/3>)

## [1.5.5] - 服务发布

### 特征

- 改进默认的 php-fpm.conf 文件。 （＃2318，＃2343）
- 当挂起的用户尝试登录时通知用户（#2310、#2345）
- 允许设置快速安装的默认 Web 安装模板 (#2344) (<https://github.com/hestiacp/hestia-quick-install>)
- 提高 apt 密钥下载方式的安全性 #2299 (<https://blog.cloudflare.com/dont-use-apt-key/>)
- 允许用户在 Web GUI 中设置系统 php 版本 (#2357)
- 在列表服务中添加了指向防火墙的链接 (#2371) @fra81

### 变化

- 修改模板警告#2346
- 从默认接受的 IP 列表 API 中删除了 127.0.0.1。 (#2325)
- 更新翻译

### Bug修复

- 更新 CSS 以防止在电子邮件信息框中换行 (#2353) @chriscapisce
- 删除有关 PhpMyAdmin SSO 的不需要的调试信息，导致电子邮件发送给管理员
- 允许使用 8 个名称服务器作为 DNS 模板（Gmail、Zoho 和 Office 365）（#2369、#2370）
- 修复了数据库需要自定义设置时无法备份的问题
- 允许用户再次编辑default.pkg。 在新安装时，默认管理员用户将被分配一个新的 system.pkg (#2365)
- 禁用 Api 时禁用启用 PMA SSO + 添加了常见问题解答的链接。 (#2365)
- 删除 error_reporting(null) 并允许将所有错误记录在 /var/log/hestia/nginx-error.log (#2365)
- 修复了值“允许暂停未保存”的问题（#2356、#2355）
- 修复了 AUTH_USER 和 AUTH_HASH 不存在且在重建期间导致 Nginx 出现问题的问题（#2350、#2355）

### 依赖关系

- 将 PHPmyadmin 更新至 5.1.2 (<https://www.phpmyadmin.net/files/5.1.2/>)
- 将 Filegator 更新至 7.7.1 (<https://github.com/filegator/filegator/releases/tag/v7.7.1>)
- 将 B2CLI 更新到 3.2.0 (<https://github.com/Backblaze/B2_Command_Line_Tool/releases/tag/v3.2.0>) (#2349) @ptrinh

## [1.5.4] - 服务发布

### 特征

### Bug修复

- 修复了 v-add-sys-phpmailer 未正确更新的问题 (#2336)
- 修复了用户无法通过 UI 下载备份的问题 (#2335)
- 修复了 php8.0“四舍五入”为 php8 导致 default.tpl 回落到 8.1 的问题 (#2340)
- 修复了重新计算磁盘使用情况的问题 (#2341)
- 修复了 php 文件在 WordPress 上传文件夹中仍然可执行的问题
- 修复了包含修订版 (-x) 的版本号无法正确构建的错误

## [1.5.3] - 服务发布

### 特征

### Bug修复

- 修复了磁盘大小计算中排除暂停对象的问题 (#2312 #2313)
- 修复了启用 2FA 时用户无法自行编辑的问题 (#2314 #2316)
- 修复了 v-add-user-sftp 中 ftp 用户未被识别为有效 sftp 监狱设置的问题 (#2308 #2319)
- 修复了“预览”功能被禁用时保持预览功能启用的问题（#2322 #2323）
- 限制访问 openbase 目录 hestia-php
- 修复了安装 nginx 命令后未找到电子邮件发送的问题 (#2328)

### 依赖关系

- 将 hestia-nginx 包的 PCRE 8.84 更新为 PCRE2 10.39
- 将 Roundcube 更新至 1.5.2（[发行说明](https://github.com/roundcube/roundcubemail/releases/tag/1.5.2)）
- 将 PHPMailer 更新至 6.5.3 ([发布消息](https://github.com/PHPMailer/PHPMailer/releases/tag/v6.5.3))

## [1.5.2] - 服务发布

### 特征

- 现在可以从通知面板获取发行说明（#2276）
- Web 域别名现在显示在域列表中 (#2278 / #2289)
- 如果未安装，DNS、邮件和数据库部分现在将隐藏在 /edit/server 中 (#2300)
- 土耳其已添加为 ipset 的选项 (#2294)

### Bug修复

- 整体代码质量得到了改进（#2293、#2298、#2307）
- 添加了对自动化测试套件 (bats) 的改进 (#2280)
- 澄清了升级过程中显示的文本 (#2270)
- 更新了 Web 域模板以允许使用 .user.ini (#2267 / #2269)
- 修复了构建过程中 Debian 上的 curl 符号链接的问题 (#2275)
- 修复了关闭网络邮件 SSL 时 CAA 记录被删除的问题 (#2279)
- 修复了使用 IDN 域时电子邮件验证失败的问题 (#2273)
- 更改行为以防止安装 modphp 时 php-fpm 重新启动 (#2270)
- 修复了 Debian 11 上密码可能无法正确设置的问题 (#2270)
- 修复了命令路径 v-change-firewall-rule 的问题 (#2249)
- 修复了“v-backup-user”中的一个问题，您可能会遇到错误“check_result 的参数无效”(#2284)
- 修复了影响 Nextcloud/Owncloud 性能的问题[论坛帖子](https://forum.hestiacp.com/t/tip-create-a-nginx-template-for-nextcloud-to-let-synchronize-files-bigger-than-10mb/5123)
- 修复了安装时未正确设置 HestiaCP 访问端口的问题 (#2288 / #2291)
- 修复了管理员无法在面板中以暂停用户身份登录的问题（#2286 / #2289）
- 修复了编辑用户界面中的“删除”按钮无法按预期工作的问题（#2282 / #2289）
- 修复了使用 ipset 编辑现有防火墙规则失败的问题 (#2292)
- 修复了未安装额外 php 版本时 /edit/server 中可能出现的错误 (#2289)
- 修复了通过 Safari 访问面板会导致错误 NSPOSIXErrorDomain:100 的问题 (#2274)
- 更正了 v-delete-dns-records 中的命令语法 (#2295)
- 修复了保存更改时 API 允许的 IP 列表值会丢失的问题 (#2296 / #2300)
- 修复了调试模式选项在发布版本中未显示并且在保存服务器设置时会重置的问题 (#2300)
- 修复了 grep 会出现的问题d 第一次添加 ipset 规则时抛出错误 (#2307)
- 修复了不正确的变量拼写 ($v_interace > $v_interface) (#2307)
- 更新了邮件域模板
- 更新了 docs.hestiacp.com 的命令行示例
- 修复了启用强制 ssl 和/或重定向时 Lets encrypt 无法获取有效 ssl 证书的问题 (#2176 / #2304 / #2304)
- 修复了 v-list-sys-dns-status 中的问题

### 依赖关系

## [1.5.1] - 服务发布

### Bug修复

- BlackBlaze 添加 B2 删除文件支持
- 在新选项卡或窗口中打开 phpmyadmin #2250 @manuelserol
- 修复 ipset 无法正常工作的问题 [论坛](https://forum.hestiacp.com/t/error-ipset-object-not-found/5015)
- 改进多个服务器上 SSH #2242 和 #2255 的端口检测
- 修复了配置文件中 # 的问题
- 修复了安装程序中的多个错误
- 设置正确的权限/install/deb/文件夹
- 调整 /etc/apt/sources.list.d/hestia.list 以包含架构，以解决 apt.hestiacp.com 中缺少 I386 的问题
- 回退到主机名，无需在 exim 中重试 ptr 查找 (#2259)
- 当启用 sieve @madito 时，在 dovecot 中启用配额
- 无法编辑 php8.1 服务 #2261

### 依赖关系

- 将 Roundcube 更新至 1.5.1 [发布通知](https://roundcube.net/news/2021/11/28/update-1.5.1-released)

## [1.5.0] - 主要版本（功能/质量更新）

### 重大变更

- **注意：** 已对 phpmyadmin/phppgadmin 配置包含在 apache2 配置中的方式进行了更改。 要恢复到旧的行为，请在 /etc/apache2/apache2.conf 中的 `IncludeOptional conf.d/*.conf` 下面添加 `IncludeOptional conf.d/*.inc` 并重新启动服务器。
- **注意：** 适用于 arm64 的 Hestia 软件包已添加到 atp.hestiacp.com，请改用普通安装说明！ 对于当前 ARM 安装以启用自动更新，
请删除 /etc/apt/sources.list.d/hestia.list 中的 `#` `# deb https://apt.hestiacp.com/ focus main` 变为 `deb https:// /apt.hestiacp.com/ focus main` 然后运行 `apt update && apt update -y`
- **注意：** 确保您的服务器/VPS 具有有效的 PTR 记录，否则您将无法发送任何邮件！

### 特征

- 添加对 Dovecote Sieve #2163 (@gejobj) => [如何启用 Managesieve](https://hestiacp.com/docs/server-administration/email.html#how-can-i-enable-managesieve) 的支持
- 改进基于 HELO 的系统并使用 RDNS 查找代替我们的旧系统
- 添加对 PHP 8.1 的支持 #2233
- 将新安装的默认 php 版本设置为 PHP 8.0
- 添加对 ARM64 处理器的支持
- 禁用 Apache2 中通过 IP 地址访问 phpmyadmin/phppgadmin #2072

### Bug修复

- 当 POLICY_SYSTEM_PASSWORD_RESET = no 时禁用 /reset/ 端点 #2167
- 添加速率限制忘记密码#2199
- 在 v-change-dns-records 未进行任何更改后防止 SOA 计数
- 修复 #1296 日志轮换在 Ubuntu 20.04 和 Debian 11 上不再轮换日志
- 运行 shellcheck 以提高代码质量
- 改进文件管理器的 ssh 端口检测。 允许用户使用自定义端口创建 /etc/ssh/sshd.conf.d/custom.conf
- 修复了 v-add-letsencrypt-host 中由于 Lets Encrypt 的更改导致速率限制问题而导致的错误
- 改进 Hestia 更新流程并允许版本决定是否需要重建
- 为自行生成的 ssl 证书添加下载 SSL 证书功能 #2181
- 阻止 Nginx + Apache2 访问 .user.ini #2179
- 添加对下载 B2 备份到本地服务器的支持以允许恢复#2199
- 更新旧安装的权限 /var/log/roundcube #2173
- 更新翻译
- 修复 Roundcube 权限
- 将 .webp 添加到浏览器可以缓存的媒体格式列表中
- 在演示模式下禁用 /list/log/auth
- 修复 #1139 强制重建 webmail 配置文件
- 修复重建 mysql 数据库@depca 中的错误
- 修复#1239 基本身份验证中的错误无法正常工作
- 在为管理员帐户安装服务器之前添加对电子邮件地址的验证
- 修复 v-change-domain-owner 中的错误 #2210
- 改进输入验证添加/编辑用户包并改进读取配置文件以防止安全问题。

### 依赖关系

- 将 Roundcube 更新至 1.5.0 <https://roundcube.net/news/2021/10/18/roundcube-1.5.0-released>
- 将 jQuery UI 更新到最新版本 [CVE-2021-41182](https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2021-41182)

## [1.4.17] - 服务发布

### Bug修复

- 修复 nginx 和 phmyadmin 未加载的错误
- 修复#2166 搜索功能损坏
- 将快速安装程序更新到最新版本

## [1.4.16] - 服务发布

### Bug修复

- 修复由于 /etc/apache2/conf.d/phpmyadmin.conf 中的规则导致 .json 未在 Apache2 上加载的错误

## [1.4.15] - 服务发布

＃＃ 特征

- 添加模板 Chevereto #2153 @ManualRechkle

### Bug修复

- 修复了 netplan 处于活动状态时 v-add-sys-ip 中的错误
- 限制对默认 /phpmyadmin 不需要的文件/文件夹的访问（\*.json、模板、区域设置、供应商）#2143
- 更新翻译
- 修复 Exim 4.94 和自动回复问题 #2151
- 修复多个用户界面错误#2415
- 修复文档 #2142 的损坏链接
-改进对 MariaDB #2141 的检测，感谢 @gejobj

## [1.4.14] - 服务发布

### Bug修复

- 修复了编辑包的问题
- 修复了 v-update-letsencrypt 和 v-restart-service 的问题
- 修复了 v-add-sys-ip 和 Ubuntu 未启用 netplan 的问题
- 修复了损坏的 UPGRADE_MESSAGE 变量未显示在电子邮件中的问题
- 更新期间包含/扩展配置备份系统

## [1.4.13] - 服务发布

### 特征

- 引入 UPGRADE_MESSAGE 变量以支持电子邮件升级通知中的自定义消息。

### Bug修复

- 改进主机名检查以防止无效主机名或使用 IP 地址 (RFC1178)。
- 防止来自其他域/网站的 CSRF
- 修复 #2096 主机名 SSL 被 mail.hostname.com 证书覆盖
- 添加小等待 /usr/bin/iptables-restore [论坛](https://forum.hestiacp.com/t/clean-install-arm64-does-not-start-after-reboot-v-start-service-iptables/4395/7) + 修复了 v-add-firewall / v-delete-firewall 功能 (#2112) @myrevery
- 修复 v-change-sys-api 中的错误。 当使用 v-change-sys-api 删除然后 v-change-sys-api 启用 + 自定义发布分支时，api 重置失败 + 没有产生“错误”输出
- 改进错误报告PMA单点登录功能功能
- 修复了 v-change-web-domain-name 中由于旧配置文件未正确删除而导致 Web 服务器无法启动的问题 #2104
- 修复了 /list/keys/ @wtwwer 中潜在的 XSS 漏洞 [披露](https://huntr.dev/bounties/0fefa2f6-7024-44c8-87c7-4d01fb93403e/)
- 删除了 /edit/file，因为它已被 Filegator 和旧 Vesta Filemanager 的一部分取代
- 修复了 /add/package @wtwwer 中潜在的外部控制/路径漏洞 [披露](https://huntr.dev/bounties/e0a2c6ff-b4fe-45a2-9d79-1f4dc1b381ab/)
- 添加额外的检查以防止类型杂耍@vikychoi [披露](https://huntr.dev/bounties/c24fb15c-3c84-45c8-af04-a660f8da388f/)
- 改进并更新了一些缺失的翻译字符串@myrevery
- 与 Github 同步翻译

## [1.4.12] - 服务发布

### Bug修复

- 允许使用自己的证书自定义邮件域#2061 @myrevery
- 修复了 v-list-dns-records 中用 \u0009 替换表格的问题 #2089 @codibit
- 修复#2087 Exim 4.94 未向远程服务器发送任何电子邮件。
- 修复了 #2082 v-delete-web-php 总是创建新的配置文件
- 在 open_basedir 中添加 /home/user/.composer #2088 @anvme

## [1.4.11] - 服务发布

### 特征

- 添加了对 Debian 11 (Bullseye) 的支持 #1661
- 在 hestia-php 中添加了对 openssl 的支持
- 使用 hestia-php 安装依赖项以解决用户配置问题（需要 hestia-php 7.4.22）
- 用 systemd 服务/启动脚本替换旧的防火墙系统 #2064 @myrevery
- 添加 GravCMS、Docuwiki 和 Mediawiki 的快速安装程序 (#2002) @PsychotherapySam

### Bug修复

- 改进 Roundcube #1917 的操控升级
- 修复版本高于 1.x.10 时更新脚本排序的问题
- 允许域使用多个 CAA 记录。 第2073章
- 将缺失的组 (www-data) 添加到 migrate_phpmyadmin 脚本 #2077 @bet0x
- 修复了 <news@domain.com> 转发到 /var/spool/news 的问题
- 与 HestiaCP 同步翻译（IT、PL、RU、SK 和 ZN-CN 更新）

## [1.4.10] - 服务发布

### 特征

- 添加了 v-delete-firewall-ban ip all #2031
- 包括 nginx/apache2 模板的配置测试

### Bug修复

- 修复了将 jQuery + jQuery UI 升级到最新版本后的 UI 问题（#2021 和 #2032）+ [论坛](https://forum.hestiacp.com/t/confusion-about-send-welcome-email-checkbox/4259/11)
- 修复了 Nginx 用作反向代理时缓存模板的安全问题
- 修复了删除多个邮件帐户的问题 (#2047)
- 修复了 phpmailer + 非拉丁字符的问题（#2050）感谢@Faymir
- 删除 CraftCMS 的缓存模板 (#2039) @anvme
- 修复了 phpmailer + 非拉丁字符的问题（#2050）感谢@Faymir
- 修复 php 重新安装后无法加载动态库 'pdo_mysql.so' (#2069)

## [1.4.9] - 服务发布

### Bug修复

- 由于 jQuery 中的漏洞，将 jQuery 和 jQuery UI 更新到最新版本。 @dependabot
- 修复了新安装的 /etc/dovecot/conf.d/10-ssl.conf 中的错误
- 修正了通知的错误
- 修复了翻译字符串@myrevery

## [1.4.8] - 服务发布

### 特征

- 添加对使用 @drone 对 HestiaCP 代码进行自动化测试的支持
- 添加对内部电子邮件 SMTP 服务器的支持 #1988 @Myself5 / #1165

### Bug修复

- 由于 jQuery 中的漏洞，将 jQuery 和 jQuery UI 更新到最新版本。 @dependabot
- 解决 hestia.conf 中双 ENFORCE_SUBDOMAIN_OWNERSHIP 键的问题
- 解决某些情况下安装期间创建新用户的问题#2000
- 修复了名为 Test123 的快速安装应用程序的问题 (@PsychotherapySam)
- 修复 dovecot 2.3 ssl 配置的问题 (#1432)
- 在升级脚本期间加载 $HESTIA 路径 (#1698)
- 从 Proftpd 配置中删除 TLS 1.1 (#950)
- 未安装 Exim 时不要删除 postfix (#1995)
- 修复一个no-php Nginx FPM 模板中的错误 (##2007)
- 更新德语翻译
- 修复了 Mail DMS 记录中的一些小错误 (#2005)

## [1.4.7] - 服务发布

### Bug修复

- 修复了 #1984 phppgadmin 在 apache2 系统上无法工作的问题
- 修复了 #1985 重新启动服务不起作用

## [1.4.6] - 服务发布

### 特征

- 添加对自定义安装挂钩的支持#1757
- 添加 CraftCMS #1973 @anvme 模板
- 将 Filegator 升级到 7.6.0

### Bug修复

- 修复了 #1961 仅续订 Apache2 SSL 证书失败的问题
- 修复了#1956 以防止重置定义的网络邮件客户端。
- 明确禁用 cron 报告#1978
- 修复了在极少数情况下证书无法安装 @dpeca 和 @myvesta 的问题
- 修复了缺少 .composer 文件夹时 Composer 安装失败的问题
- 修复了#1980 Lets Encrypt Auto Renewal 将 Webmail 客户端恢复为 Roundcube

## [1.4.5] - 服务发布

### Bug修复

- 恢复 #1943 并重新设计它以修复 v-rebuild-cron-jobs 上可能发生的错误。
- 修复了#1956 以防止重置定义的网络邮件客户端。
- 明确禁用 cron 报告#1978

## [1.4.4] - 服务发布

### 特征

- 将 nginx user_agent 分离添加到桌面/移动设备（例如用于 fastcgi 缓存）
- 在 www-data 用户而不是“用户”下运行 phpmyadmin 文件夹，提高安全性。 （@bet0x）
- 添加了 mod php 用户访问 phpmyadmin 的新模板

### Bug修复

- 添加禁用 Webmail 时的模板，允许生成 SSL。
- 修复了 /list/log/ 中的 PHP 错误
- 修复了 /list/services 中的时间问题，因为它显示为 50 分钟1 而不是分钟
- 添加缺失的后退按钮 + 修复登录页面上后退按钮的行为。
- 当 user.conf 中缺少 WEB_TEMPLATE 和 PROXY_TEMPLATE 时，设置“默认”
- 将 BACKEND_TEMPLATE 添加到默认包
- 修复了 v-rebuild-cron-jobs #1943 可能发生的错误（感谢 @clarkchentw）
- 当用户启用 SSH 时限制访问文件管理器 (@bet0x)
- 运行 v-change-sys-ip-nat 时检查 DNS 域 (@clarkchentw)
- 修复了安装程序中的逻辑错误（@clarkchentw）

## [1.4.3] - 服务发布

### 特征

- 在 DNS 记录列表中包含 DMARC 记录 #1836
- 启用 phpMyAdmin 单点登录支持 #1460
- 添加命令以从 API_ALLOWED_IP 列表中添加/删除 (#1904)

### Bug修复

- 通过排除备份中的排除文件夹、邮件帐户和数据库来改进新备份的计算磁盘大小 (#1616) @Myself5
- 改进 v-update-firewall / v-stop-firewall 以使其自我修复 (#1892) @myrevery
- 将 phpMyAdmin 版本更新至 1.5.1（参见 <https://www.phpmyadmin.net/news/2021/6/4/phpmyadmin-511-released/>）
- 修复了使用 Exim4 和暂停域重建邮件后的错误 (#1886)
- 修复了“API 允许的 IP 地址”字段的奇怪行为#1866
- 修复了由于重定向而未设置“已保存确认”的问题#1879
- 增加了 ClamD / ClamAV 的最低内存要求。 第1840章
- 备份恢复未在新帐户上重建“强制 SSL”和“HSTS”配置 #1862
- 在更新 HestiaCP 时保留 /install/upgrade/manual/install_awstats_geopip.sh 所做的更改（通过 Discord）
- 重构/改进 PHP 和 HTML 代码@s0t (#1860)
- 修复了登录页面和其他一些位置中的 XSS 漏洞@briansemrau / @numanturle
- 在 session_regenerate_id() @briansemrau 之后删除旧会话
- 改进当域已准备好存在于不同帐户上时的错误消息。
- 修复了 Postgresql 可用时 phpmyadmin 未更新的问题。
- Webmail 客户端设置为 rainloop，无法通过 LE 创建 SSL 证书 #1913
- 修复了plugin-hestia-change-pasword 未更改 v-change-sys-port (Rainloop) 上的端口的问题 #1895
- 修复了未设置 HELO 消息/在 NAT IP 上创建错误的问题

## [1.4.2] - 服务发布

- **注意：** 在 1.4.1 / 1.4.0 版本中，我们为服务器上具有多个网络端口的 Ubuntu 20.04 和 18.04 用户引入了一个错误。 本次发布将解决该bug带来的问题！ 如果您无法通过 apt 下载 Hestia 软件包。 以 root 身份通过 CLI 或 SSH 运行以下命令

```bash
iptables -A INPUT -m state --state RELATED,ESTABLISHED -j ACCEPT
```

然后通过运行更新

```bash
apt update && apt upgrade
```

### Bug修复

- 修复了 iptables/网络启动脚本的问题 (#1849) (@myrevery)
- 修复了升级 nginx 期间意外替换 nginx.conf 的问题 (#1878 / @myrevery)
- 修复了安装 Ubuntu 18.04 的问题
- 修复了以管理员用户身份登录文件管理器的问题
- 添加了 proxy_extentions 以支持旧的自定义模板
- 添加了当交互设置为“否”时跳过强制重新启动的可能性
- 修复了 modx 模板的问题
- 更新翻译（克罗地亚语、捷克语和意大利语）
- 修复了启用 POLICY_USER_EDIT_WEB_TEMPLATES 时用户无法保存/更新 Web 域的问题 (#1872)
- 修复了管理员用户无法为用户添加新的 ssh 密钥的问题 (#1870)
- 修复了domain.com不存在的问题作为有效域受到影响 (#1874)
- 修复了更新发布时未删除“开发”图标的问题（#1835）

## [1.4.1] - 错误修复

- 修复了启用 2FA 登录的错误

## [1.4.0] - 主要版本（功能/质量更新）

- **注意：** Ubuntu 16.04 (Xenial) 不再受支持，因为它已达到 EOL（生命周期结束）状态。
- **注意：** “独立”模式下的 Apache 不再受到积极支持，并且已从安装程序选项中删除。 Nginx（代理）+ Apache2 将继续受支持。
- **注意：** 由于我们处理快速安装应用程序的方式发生变化，自定义“快速安装应用程序”将不再起作用。 需要对快速安装程序应用程序进行最少的更改！ 请查看 <https://github.com/hestiacp/hestia-quick-install> 了解如何迁移！
- **注意：** 手动升级脚本可用于将 Roundcube、Rainloop 和 PHPmyadmin 更新到最新版本，这些脚本可在 /usr/local/hestia/install/upgrade/manual/ 中找到

### 特征

- 引入了对 NGINX FastCGI 缓存的支持。
- 引入了对 SMTP 中继/智能主机（服务器范围或每个域）的支持。
- 引入了选择每个域使用哪个网络邮件客户端（Roundcube 或 Rainloop）的功能。
- 添加了对 Rainloop 的支持（运行 v-add-sys-rainloop 来安装它）
- 添加了对远程备份位置的 B2 备份支持 - 感谢 **@rez0n**！
- 添加了对 osTicket 的模板支持 - 感谢 **@madito**！
- phpMyAdmin、Roundcube 和 Rainloop 的软件包将直接从其上游源中提取，而不是从 APT 进行新安装。
- 向邮件域添加了 DNS 记录视图，该视图提供 DKIM、SPF 和其他条目以供外部提供商使用。
- 添加了升级脚本以提供对 php7.4（或任何其他版本）的就地升级。
- 添加了 Drupal 和 Nextcloud 快速安装程序支持（删除了占位符 Joomla）
- 添加了新的可选主题“Vestia”
- 添加了一个开关来禁用 API，并将 API 默认限制为仅 127.0.0.1。 对于当前安装，默认添加了“允许全部”选项
- 第一次重新启动 Hestia 后将尝试 1 次尝试请求/生成有效的 Lets 加密证书
- 通过 WebUI 引入了多项新的安全策略。
  - 允许用户编辑 Web / 代理 / DNS / 后端模板
  - 允许用户编辑帐户详细信息
  - 允许暂停的用户以“只读”访问权限登录
  - 允许用户查看/删除用户历史记录
  - 强制执行子域所有权
  - 当其他用户分配有“管理员”角色时，限制对管理员帐户的访问。
- 禁止用户通过 WebUI 登录/限制每个用户的特定 IP 地址访问 WebUI。
- 不鼓励在“管理员”帐户下创建网站，并重定向用户以创建新用户。
- 添加了对重定向到 www /非 www 域（或自定义）#427 / #1638 的支持。
- 允许用户查看该帐户的失败登录尝试。
- 引入了对基于 ARM 的系统的支持。 目前，这些软件包无法通过 ATP 获得！
- 安装后强制重新启动系统

### Bug修复

- 修复了编辑 FTP 用户时用户名重复的问题。 (#1411)
- 修复了当fail2ban停止时iptables服务似乎处于停止状态的问题。 (#1374)
- 修复了服务器设置 > 配置下默认语言值设置不正确的问题。
- 修复了深色主题中错误显示可用更新的问题。
- 修复了运行“v-delete-user-backup”时未删除本地和 FTP 备份文件的问题。 (#1421)
- 修复了无法删除 IP 地址的问题。 (#1423)
- 修复了“v-rebuild-user”除了用户帐户配置之外还会错误地重建域项目的问题。
- 修复了从备份恢复时导致 Web 域的自定义文档根值丢失的问题。
- 修复了使用 Safari/iOS 时导致“NSPOSIXErrorDomain:100”错误的问题（感谢 **@stsimb**）。
- 修复了 exim 忽略配置的邮件配额限制的问题。
- 修复了编辑邮件自动回复时执行无效字符验证的问题。
- 修复了使用 Moodle 模板时导致 Let's Encrypt 失败的问题（感谢 **@ArturoBlanco**）。
- 修复了由于错误的正则表达式属性而未保存 MySQL `wait_timeout` 值的问题（感谢 **@guicapanema**）。
- 修复了 nginx web 统计授权文件放置在错误目录中的问题。
- 修复了使用 PostgreSQL 时报告的几个小问题。
- 提高了邮件域和网络邮件客户端的可靠性。
- 提高了升级期间服务重启的可靠性。
- 改进了与 Blesta / WHMCS 插件的兼容性。
- 改进了 API 错误处理例程 - 感谢 **@danielalexis**！
- 在使用“zstd”压缩类型创建存档时，通过使用多线程提高了备份性能。
- 改进了创建防火墙规则时的错误处理。
- 改进了对暂停用户和域的处理，以允许在不取消暂停的情况下进行删除。
- 改进了对 pac 的依赖性kage 控制安装 `lsb-release` 和 `zstd`。
- 改进了 SFTP 连接处理，使其不区分大小写（感谢 **@lazzurs**）。
- 改进了域验证，以防止在顶级域属于另一个帐户时创建子域（感谢 **@KuJoe** 和 **@sickcodes**）。
- 改进了 IDN 域处理，以解决 Let's Encrypt SSL 和邮件域服务的问题。
- 为所有主模板的 openbasedir 权限添加了私有文件夹。
- 禁用通过 Web UI 更改备份文件夹，因为它使用符号链接而不是安装，导致恢复邮件/用户文件出现问题。
- 修复了“v-add-sys-ip”和用户历史日志中的 XSS 漏洞（感谢 **@numanturle**）。
- 修复了删除 SSH 密钥时可能发生的远程代码执行漏洞（感谢 **@numanturle**）。
- 修复了 v-update-sys-hestia 中的漏洞（感谢 **@numanturle**）
- 由于超时问题，禁用了通过 WebUI 的更新。 请通过命令行中的“apt update && apt update”进行更新。
- 改进 Web 应用程序快速安装的处理方式，并允许用户在列表视图中维护添加的应用程序。
- 修复了 HestiaCP 更新后启用 api 的问题
- 修复了默认 php 版本被删除时 webmail 不再工作的问题。 第1477章
- 启用“演示”模式时限制访问。
- 修复了别名限制无法正常工作的问题
- 修复了“退出控制面板”链接更改为“注销”的问题#1669
- 允许在使用时删除包。 当前用户已更改为“默认”包。
- 修复了 v-restore-users 中的多个错误
- 重新设计静态页面
- 允许使用别名创建自签名证书。
- 修复了邮件帐户按大小排序不正确的问题#1687
- 改进结果 v-search-command #1703
- 合并 Codeiginiter / Drupal 模板。
- 为 FastCGI 支持准备模板，通过仅允许 .well-known 来加密请求，从而提高安全性
- 更新 nginx.conf 中的 Cloudflare Ips
- 修复了数据库连接失败时电子邮件发送给任何人的问题 #1765
- 修复了远程备份失败时不会发送失败通知并保存本地备份的问题。
- 修复了顶级域中包含 2 个点的域可能会意外被删除的问题 #1763
- 修复了可以创建 www 且删除后 webmail 不再工作的问题 #1746
- 标准化升级脚本的标头
- 改进了我们处理自定义主题的方式
- 重构HMTL/PHP代码WebUI
- 更新了 ClamAV 配置
- 修复了文件管理器密钥获得错误权限的问题
- 更新版本 Laveral @mariojgt

## [1.3.5] - 服务发布

### 特征

- 此版本中没有引入新功能。

### Bug修复

- 从packages.sury.org (<https://forum.hestiacp.com/t/apt-upgrade-failed-gpg-error-packages-sury-org>) 更新了 PHP 的 APT 存储库密钥
- 将 phpMyAdmin 更新至 v5.1.0。

## [1.3.4] - 服务发布

### 特征

- 此版本中没有引入新功能。

### Bug修复

- 修复了 v-add-sys-ip 和用户历史日志中的 xss 漏洞（感谢 **@numanturle**）
- 修复了删除 ssh 密钥时远程执行的可能性（感谢 **@numanturle**）

## [1.3.3] - 服务发布

### Bug修复

- 如果 Web 文件夹已存在并且不遵循 chmod 上的符号链接，则进行了改进（感谢 @0xGsch 和 @kikoas1995）。
- 改进了 api 密钥身份验证，以防止暴力攻击。
- 改进了 ssh 密钥文件夹权限以防止未经授权的访问。

## [1.3.2] - 服务发布

### 特征

- 添加了对 multiphp 环境的 PHP v8.0 支持。

### Bug修复

- 得益于漏洞实验室 - [Evolution Security GmbH]™，改进了登录功能中的会话令牌处理。
- 修复了更改后端模板时未删除 fpm 池配置的问题。
- 使用 multiphp (5.6-8.0) 测试改进了蝙蝠测试。
- 修复了完整网络邮件路径加载为默认值的问题。

## [1.3.1] - 服务发布

### 特征

- 此版本中没有引入新功能。

### Bug修复

- 修复了由于我们的服务和包版本控制方案的更改，“hestia-php”的更新在 UI 中被错误地标记为过时的问题。
- 修复了“更新”页面上出现的问题，即可用更新的表格行颜色难以阅读。
- 修复了管理员在添加 SSH 密钥后尝试返回时会陷入循环的问题。
- 修复了超过表格长度的长表格条目会与其他 UI 元素重叠的问题。
- 修复了页面上的项目总数无法正确显示的问题。
- 提高了整个控制面板 UI 中工具提示的准确性和可靠性：
  - 从按钮和其他元素中删除了不必要的工具提示。
  - 修复了导致工具提示无法显示的错误标签。
  - 引入了工具提示来计数器“用户”、“包”和“统计”页面上的项目，以帮助更好地进行操作r 区分统计。
- 改进了控制面板导航标题中项目、配额和暂停项目的显示 - 感谢 **@cmstew**！
- 修复了由于重建过程中的重复情况而导致升级期间 CPU 使用率高于正常水平的问题。
- 修复了命令行脚本注释和输出文本中的轻微拼写不一致问题。
- 修复了使用“v-change-domain-owner”移动域时未清理旧配置文件的问题。
- 修复了使用旧模板升级后可能会出现“不存在后端模板”的问题 (#1322)。
- 引入了 nginx + php-fpm 配置的缓存模板 - 感谢 **@cmstew**！
- 修复了由于可用区域中 DKIM 记录的格式而导致 DNS 集群更新可能失败的问题 - 感谢 **@jrohde**！
- 提高了命令行脚本中注释格式的质量 - 感谢 **@bisubus**！
- 修复了文件管理器中未显示徽标的问题 - 感谢 **@robothemes**！
- 修复了控制面板 UI 中的一个问题，如果手动添加用户名，该问题会导致数据库和其他 FTP 帐户命名不正确。
- 修复了自定义文档根目录未正确保存的问题。
- 提高了控制面板 UI 中服务可用性的可见性。
- 修复了允许您在活动演示模式下取消暂停 cronjob 的问题。
- 更新了 DE、EN、ES、KO、NL 和 TR 语言，感谢 @Wibol、Blackjack、@emrahkayihan、areo 和 @hahagu！
- 修复了本地 src 构建时自动编译器失败的问题。
- 向系统安装程序添加了土耳其语言，感谢@emrahkayihan！
- 修复了将未知域与 v-delete-domain 一起使用时的错误消息。

## [1.3.0] - 主要版本（功能/质量更新）

### 特征

- 用户现在可以选择将域指向不同的文档根位置（类似于域停放）。
- 软件更新过程现在将在继续安装之前执行系统运行状况检查。
- 管理员现在可以通过“$HESTIA/conf/hestia.conf”中的以下设置以及控制面板 Web 界面来控制软件更新通知：
  - `UPGRADE_SEND_EMAIL` = 向主管理员帐户的电子邮件地址发送电子邮件通知
  - `UPGRADE_SEND_EMAIL_LOG` = 将安装日志输出发送到主管理员帐户的电子邮件地址
- 升级过程现在默认将安装日志保存到“/root/hst_backups”目录，以用于安装后故障排除。
  - **注意：** 我们将来可能会调整此路径，并将记录发生的此类更改。
- 我们引入了向其他用户帐户分配管理员权限的功能，使他们能够在“服务器设置”选项卡下执行任务。
- 我们引入了更强大的翻译系统，这将使我们能够在未来的版本中提供更高质量的翻译。
  - **注意：** 某些国家/地区代码已更新，因此升级后您的语言设置可能会默认恢复为英语。
- 对于新安装，MariaDB 10.5 现在是默认版本。
  - 对于现有安装，我们提供了手动安装后升级脚本。 请运行 `$HESTIA/install/upgrade/manual/upgrade_mariadb.sh` 以迁移到 MariaDB 10.5）。
- 用户界面主题默认设置为“深色”。 这可以从 **服务器设置 > 配置 > 基本选项 > 外观** 进行更改。
  - **注意：** 默认主题的名称尚未调整，对“深色”主题的更改仅适用于此时的新安装。 此行为可能会在未来版本中更改。

### Bug修复

- 修复了可能从系统进程列表中收集用户密码重置密钥的安全问题 - 感谢 **RACK911 LABS**
- 修复了密码包含“`'`”的问题 - [论坛](https://forum.hestiacp.com/t/two-factor-authentication-issue-with-standard-user/1652/)
- 修复了未指定端口时数据库备份的问题 (#1068)
- 修复了未启用 SSL 的网站将显示第一个启用 SSL 的有效网站的内容的问题 (#1103)
- 修复了在安装程序中使用“--with-debs”标志时由于版本检查例程不正确而出现的问题 (#1110)
- 修复了恢复电子邮件帐户时可能出现的权限不正确的问题 (#1114)
- 修复了文件管理器对新目录应用错误权限的问题
- 修复了阻止从备份存档成功恢复启用 SSL 的邮件域的问题 (#1069)
- 修复了 phpMyAdmin 按钮在控制面板 Web UI 中不起作用的问题 (#1078)
- 修复了密码生成不正确的问题 (#1184)
- 修复了“v-add-sys-ip”中的问题，以确保 IP 配置设置为正确的端口 - 感谢 **@madito**
- 修复了运行“v-rebuild-all”时导致循环条件延长的问题
- 改进了对“v-add-remote”API 密钥使用的支持-dns-host` 命令 (#1265)
- 改进了执行备份例程时可用磁盘空间的验证（#1115）
- 改进了对 RSA / DSA 之外的 SSH 密钥类型的支持
- 提高了删除远程位置时备份功能的可靠性（#1083）
- 通过在 exim 的黑名单中添加额外的已知危险文件扩展名来改进垃圾邮件过滤 (#1138) - 感谢 **@kpapad904**
- 更新了 Apache2 配置以使用 Include 和 IncludeOptional (#1072)
- 删除了以“root”身份登录的功能（登录到管理员帐户，认为不再需要）
- 将 ca-certificates、software-properties-common 添加到依赖项中（#1073 + [论坛](https://forum.hestiacp.com/t/hestiscp-fails-on-new-debian-9-vps/1623/8) - 谢谢 **@daniel-eder**
- 创建新用户帐户时默认创建 .npm 目录 (#1113) - 感谢 **@hahagu**
- 提高了多个 UI 翻译（NL、DE、UK、RU、ES、IT、ZH-CN）的准确性 - 感谢 **@myrevery** 和其他贡献者的工作！
- 在“v-add-web-domain-backend”命令中添加了“$restart”标志 (#1094) (#797) - 感谢 **@bright-soft**
- PostgreSQL：禁止使用大写字母（#1084），导致备份/创建数据库或用户出现问题
- 更改了 Quick Web App 安装程序中的 WordPress 名称 (#1074)
- 清理了 Google / Gmail DNS 模板中使用的条目 - 感谢 **@madito**
- 增强了 ProFTPd 对 TLS 的支持
- 重构LXD编译脚本
- 将 phpMyAdmin 更新至版本 5.0.4

## [1.2.4] - 服务发布

### 特征

- 此版本中没有引入新功能。

### Bug修复

- 修复了自动续订让我们加密证书的问题。

## [1.2.3] - 服务发布

### 特征

- 此版本中没有引入新功能。

### Bug修复

- 修复了密码字段中拒绝非 ASCII 字符的问题。

## [1.2.2] - 服务发布

### 特征

- 此版本中没有引入新功能。

### Bug修复

- 如果 mailhelo.conf 不存在，则创建它以防止在 grep 期间出现错误消息。
- 更正了 DNS 记录类型的显示，使其按字母顺序显示。
- 修复了如果添加新 DNS 记录时发生错误，DNS 记录类型字段将重置的问题。 (#992)
- 修复了编辑 DNS 记录时 DNS 域提示无法正确显示的问题。 (#993)
- 修复了如果从 A 更改为 CNAME，DNS 记录会出现格式错误的问题。 (#988)
- 修复了 DNS 记录页面上后退按钮的问题。 (#989)
- 修复了由于虚拟主机配置不正确而导致 phpMyAdmin/phpPgAdmin 无法正确加载的问题。 (#970)
- 修复了存在自定义主题文件时返回格式错误的 JSON 输出的问题。 (#967)
- 修复了如果 .bash_aliases 不存在则首次运行“v-change-user-php-cli”时会出现的错误。 (#960)
- 纠正了将鼠标悬停在顶级菜单项上时不显示工具提示的问题。
- 改进了安装过程中 APT 存储库密钥的处理。
- 重新设计了 Let's Encrypt 续订功能以跳过已删除的别名。
- 提高了使用 IP 列表时列表处理的可靠性。
- 通过密码强度的视觉指示强制执行最低密码要求。
- 修复了更改包时用户显示名称值设置不正确的问题。
- 改进了安装程序版本检测。
- 改进了对 MariaDB 和 MySQL 服务的检测。

## [1.2.1] - 服务发布

### 特征

- 将名字和姓氏字段合并为单一名称字段，以便于输入。
  - v-change-user-name 现在将接受“First Last”（单个参数）和 First Last（两个参数）以实现向后兼容性。
- 从新安装中删除了 ntpdate 并启用 systemd 时间同步守护进程（感谢 **@braewoods**）

### Bug修复

- 修复了 Composer 由于缺少默认目录而无法安装的问题。
- 纠正了双因素身份验证验证在登录过程中导致 CPU 负载过高的问题。 登录屏幕已重新设计为多步骤流程（用户名 > 密码 > OTP PIN）。
- 纠正了登录屏幕上的文本输入字段默认情况下不会自动聚焦的问题。
- 修复了如果 dig 无法解析服务器名称，则 RDNS 值设置不正确的问题。
- 修复了使用保加利亚语作为显示语言时标题中的图标被下推的问题。 (#932)
- 修复了运行 v-schedule-user-backup-download 时未创建新备份的问题。 (#918)
- 修复了默认配置文件和模板未正确备份的问题。
- 提高了 Drupal 默认 Web 域模板的质量。 (#916)
- 在翻译文件中添加了缺失的字符串（翻译如下）。
- 纠正了使用保加利亚语或希腊语时，由于字符串长度原因，工具栏在“邮件”和“防火墙”页面上不合适的问题。
- 改进了西班牙语翻译（感谢 **@Wibol**）
- 改进了德语翻译（感谢 **@ronald-at**）
- 改进R俄语翻译（感谢 **@Pleskan**）

## [1.2.0] - 主要版本（功能/质量更新）

### 特征

- **注意：** Debian 8 不再受支持，因为它已达到 EOL（生命周期终止）状态。
- 添加了对 Ubuntu Server 20.04 LTS 的支持。
- 添加了文件管理器功能（使用 File Gator 后端），可以随时添加或删除（“v-add-sys-filemanager”和“v-delete-sys-filemanager”）
- 扩展的内置防火墙支持使用 IP 列表允许或阻止流量。
- 通过在新安装中默认切换到 mpm_event 而不是 mod_prefork 来提高 Apache2 性能。
- 添加了对每个 DNS 记录配置单独 TTL 的支持。 感谢**@jaapmarcus**！
- 更新了波兰语（感谢@RejectPL！）、荷兰语、法语、德语和意大利语（WIP）的翻译。
- 添加了为每个用户设置默认 PHP 命令行版本的功能。
- 添加了对 awstats 的地理定位支持，以改进流量报告。
- 默认启用 Roundcube 插件 newmail_notifier 和 zipdownload。
- 添加了对多个域和 IP 的 HELO 支持。
- 添加了从 CLI 和 Web 界面管理 SSH 密钥的功能。
- 为现有安装/升级添加了 apache2 mpm_event 的手动迁移脚本 (`$HESTIA/install/upgrade/manual/migrate_mpm_event.sh`)。
- 添加了用于测试 Bash 脚本功能的 BATS 系统（WIP）。
- 添加了 **v-change-sys-db-alias** 以更改 phpMyAdmin 和 phpPgAdmin 访问点（`v-change-sys-db-alias pma/pga myCustomURL`）。

### Bug修复

- 防止更改非 Hestia 用户帐户的密码。 感谢**亚历山大·赞尼**！
- 调整 Let's Encrypt 对 IDN 域的验证检查，感谢 **@zanami**！
- 在 FTP/SFTP 恢复上设置备份下载位置，感谢 **@Daniyal-Javani**！
- 在多次连续尝试失败后，停止尝试续订 Let's Encrypt 证书。 感谢**@dpeca**！
- 修复了在 Cloudflare 代理后面使用时自动注销的问题，并重新设计了 2FA 身份验证部分。 感谢**@rmj-s**！
- 修复了如果存在相似的帐户名称，则更改电子邮件帐户密码会失败的问题。
- 修复了在（取消）暂停或重建邮件帐户时未保留电子邮件配额的问题。
- 修复了从 Web 界面编辑时当前未保存 SSH 配置的问题。
- 修复了更改 Web 域 IP 后 DNS IP 不使用可用的 NAT/公共 IP 的问题。
- 修复了启用两步身份验证时用户尝试恢复其帐户时可能出现的问题。
- 修复了恢复用户备份时出现的权限问题。
- 改进了控制面板 Web 界面的页面加载性能。
- 使用 Apache2 软件包的 Sury.org 存储库。
- 改进了与 Roundcube 和 PHP 7.4 的兼容性。
- 限制通过控制面板 Web 界面编辑 crontab 服务的能力。
- 在添加第三方存储库之前检查是否选择安装 Nginx、Apache2 和 MariaDB。
- 限制公众访问 Apache2 服务器状态页面。
- 删除默认 fpm 配置中重复的 set-cookie 行。
- 列出防火墙规则时忽略空行。
- 改进了控制面板 Web 界面中的顶级导航（“服务器”选项卡已移至“通知”图标旁边）。
- 修正了各种小的用户界面和主题问题。
- 从 Web 界面上传自定义 SSL 证书时清理临时文件。
- 添加/更新 Let's Encrypt SSL 证书时清理临时文件。
- 运行 v-list-sys-services 后清理临时文件。
- 删除了一些遗留代码和未使用的资产。
- 不计算 v-list-sys-info 中的 /home 文件夹大小。
- 调整 v-list-sys-services 以遵循更改后的fail2ban 服务名称。
- 重新设计 v-change-sys-port 中的繁忙端口验证。
- 实施验证功能以在安装之前验证 hestia.conf 中的版本是否正确。
- 当重复输入不正确的用户名、密码或 2FA 代码时会出现延迟。
- 改进的“忘记密码”功能防止暴力攻击。
- 修复了执行 v-delete-user-backup 时备份更新计数器未正确更新的问题。
- 修复了 public\_(s)html 文件所有权的问题。
- 修复了 phpPgAdmin 访问的问题。
- 修复了在某些配置上为 <www.conf> 设置了错误端口的问题。
- 修复了 Composer 安装失败的问题。
- 修复了未立即应用所选主题的问题。
- 修复了 HTTP 到 HTTPS 重定向和 HTTP 严格传输安全 (HSTS) 事件未显示在用户历史记录日志中的问题。
- 修复了 Web 域访问日志页面格式不正确的问题。
- 修复了如果 Web 域没有任何别名，awstats 将显示 HostAliases 错误的问题。
- 修复了添加或删除 Web 域别名时 awstats 配置不会更新的问题。
- 修复了用户界面元素重叠或显示在错误位置的问题使用非英语语言环境。
- 修复了如果设置了自定义 URL，则无法从 Web UI 访问 phpMyAdmin 和 phpPgAdmin 的问题。
- 修复了无法从备份存档正确恢复邮件 SSL 证书的问题。
- 修复了删除域时未删除邮件域配置文件的问题。
- 改进了“v-change-domain-owner”的功能，以正确移动邮件域并提供状态输出和日志记录/通知。
- 改进了“v-update-sys-hestia-git”的功能，以允许用户指定 GitHub 存储库以及是否仅构建核心包或核心和依赖项。
- 更正了 phpMyAdmin 和 phpPgAdmin 的行为，以便别名对话框仅接受自定义单词而不接受完整的 URL，与网络邮件别名行为保持一致。
- 更正了安装程序的行为，以便在安装因版本不匹配而中止时不会添加 APT 存储库。
- 修复了在版本之间跳转时升级过程无法正确执行的问题（例如 1.0.6 > 1.2.0）。

### 已知问题和注释

- **注意：** 在此升级期间，自定义 phpMyAdmin 和 phpPgAdmin URL 将重置一次，以纠正遗留代码问题。
- 从 Web 域中删除别名时 Let's Encrypt 续订失败 (#856)
- 一些翻译字符串需要更新以确保准确性（#746）
- v-restore-user 仅适用于存储在 /backup 安装点中的备份存档 (#641)

## [1.1.1] - 2020-03-24 - 修补程序

### 特征

- v1.1.1 没有引入新功能，这严格来说是一个安全/错误修复版本。

### Bug修复

- 修复了 phpMyAdmin 河豚和 tmp 目录问题。
- 在密码重置中添加了主机域的额外验证。 感谢@FalzoMAD 和@mmetince！
- 修复了 rc.local 无法正确执行的问题。
- 重新设计 Let's Encrypt 例程，以在验证重试之间使用渐进式延迟。
- 修复了 v-list-sys-db-status 中阻止主要功能加载的语法问题。
- 由于性能问题，运行 v-list-sys-info 时删除了 /home 大小报告。
- 更新了安装程序以将 Ubuntu 密钥服务器用于 Hestia APT 存储库。
- 修复了 v-change-user-password 中的重复演示模式检查。

## [1.1.0] - 2020-03-11 - 主要版本（功能/质量更新）

### 特征

- 添加了对自定义用户界面主题的支持。
- 引入了官方的黑暗和平主题。
- 添加了只读/演示模式 - DEMO_MODE 必须在 hestia.conf 中设置为 yes 才能启用。
- 添加了 php-imagick 模块到安装程序和升级脚本。
- 为fail2ban添加了累加过滤功能。
- 改进和重构多 PHP 功能。
- 新安装时将默认启用多 PHP。
- 允许管理员用户从 Web UI 添加/删除 PHP 版本（服务器 -> 配置 -> Web 服务器）。
- 扩展 v-extract-fs-archive 以允许存档测试并仅提取特定路径（对于 tar）
- 允许从控制台重命名现有包 (v-rename-package)。
- 将 PHP 7.4 添加到 Multi-PHP。
- 添加了对 Debian 10 (Buster) 的官方支持。

### Bug修复

- 添加了对 Web 根目录的检测，以添加 .wellknown ACME 挑战。
- 重新设计了 Let's Encrypt ACME 分段以使用 Hestia 代码标准。
- 修复了 Windows 和 Linux 上字体渲染不正确的问题。
- 修复了 Let's Encrypt 的问题 - 使用 Nginx 进行 Let's Encrypt ACME 请求（如果存在）。
- 重新设计了 v-add-sys-ip，删除了已弃用的 CentOS/Red Hat 代码并重新设计了条件。
- 启用 HSTS 并在 v-add-letsencrypt-host 上强制使用 SSL。
- 删除了 HELO 数据中的硬编码邮件（外观修复）。
- 修复了 SFTP 服务器验证检查 - 感谢@dbannik。
- 使用默认管理员帐户创建 Web 域时实施了安全警告消息。
- 修复了 v-generate-api-key 后端脚本中默认密钥文件夹位置使用的错误引号。
- 修复了允许在管理员帐户下在 Web 域中创建的 FTP 用户访问的权限。
- 在 SFTP failure2ban 监狱上设置权限之前检查用户主目录是否存在。
- 修复了几个报告的安全问题，感谢[Andrea Cardaci](https://cardaci.xyz/)
- 安全修复：命令行参数写入日志文件时会进行全局扩展。
- 删除 Web 域时，确保 SFTP 帐户在 sshd 中保持配置状态/
- 通过确保用户主文件夹中的文件操作将以真实用户身份执行来提高安全性。
- 添加了删除用户日志时的确认对话框。
- 修复了从备份存档恢复的用户帐户 SFTP failure2ban 监狱无法正常工作的问题。
- 增强了后端命令行脚本中的输入验证。
- 通过优化通知列表的加载方式，提高了页面加载性能（在某些情况下，测量时间从 1 秒缩短到 100 毫秒以下）。
- 改进了在控制面板中加载 IP 禁止规则时的页面加载性能。
- 将面板框架更新为 3.4.1，以使用 jQuery。
- 修复了由于缺少用户而导致 SFTP failed2ban 监狱的问题。
- 修复了远程备份主机名会拒绝 IP 地址而不进行逆转的问题e DNS（PTR 记录）。 (#569)
- 在用户主目录中创建默认可写文件夹（#580）。
- 添加了 gnupg/gnupg2 检查以防止 pubkey 安装出现问题。
- 修复了添加新包时的 DNS 名称服务器验证。
- 为 Let's Encrypt 验证实现了额外的调试信息 - 感谢@shakaran。
- 禁用成功的 cronjob 备份警报。
- 修复了以普通（非管理员）用户身份登录时暂停资源的问题。
- 修复了取消暂停用户、PHP-FPM 网站池配置被删除的问题。
- 修复了使用 v-update-sys-hestia-git 时潜在的升级问题。
- 修复了重建邮件域时全局用户统计数据的损坏。
- 修复了备份排除文本框的格式。
- 修复了 MultiPHP 升级脚本以更新所有 Web 模板。
- 修复了安装程序脚本中的报告问题链接。
- 修复了备份恢复时的数据库用户身份验证。
- 为 Roundcube Webmail 添加了 robots.txt 以防止搜索机器人爬行。
- 在 Let's Encrypt 认证续订时重新启用强制 ssl 功能。
- 添加了官方 PostgreSQL 存储库，以便系统保持最新的最新可用上游软件包。
- 强化MySQL配置，防止本地infile。
- 修复了 lograte 错误并清理了混乱的 nginx/apache2 日志权限。
- 修复了 apache2 模板的 IfModule mpm_itk.c。
- 为 Debian 10 添加了 mpm_itk（仅限非多 PHP 安装。）
- 强化 nginx 配置，放弃对 TLSv1.1 的支持。
- 修复了从恢复备份中排除名为“logs”的文件夹的问题，感谢@davidgolsen。
- 修复了删除 psql 数据库部分中的拼写错误，感谢@joshbmarshall。
- 将长 txt 记录拆分为 255 个块以防止绑定问题，感谢 @setiseta。
- 修复了 v-add-letsencrypt-host 上 vsftp 缺少的重启例程。
- 显示运行 v-list-sys-info 时 /home 消耗的磁盘空间量。
- 从以前的版本中删除损坏的/webmail 别名。
- 使用多个 IP 时，Webmail IP 地址现在从 Web 域继承。
- Exim 现在使用 Web 域 IP（如果存在）。
- 修复了使用 Office 365 模板的 DNS 域的错误 MX 记录。

## [1.0.6] - 2019-09-24 - 修补程序

### Bug修复

- 添加对 HTTP/2 Let's Encrypt Server 的支持。

## [1.0.5] - 2019-08-06 - 修补程序

### Bug修复

- 修复了几个安全问题，感谢 [Andrea Cardaci](https://cardaci.xyz/)
- 返工 Let's Encrypt ACME staging 以使用符合标准的 hestia。
- 修复了 if 条件，使用 nginx 进行 Let's Encrypt ACME 请求（如果存在）。

## [1.0.4] - 2019-07-09 - 修补程序

### Bug修复

- 延迟启动服务以防止重新启动限制。

## [1.0.3] - 2019-07-09 - 修补程序

### Bug修复

- 修复了 Let's Encrypt Mail SSL 权限问题。

## [1.0.1] - 2019-06-25

### 特征

- 改进了对 Let's Encrypt 证书生成的支持。
- v-add-letsencrypt-host：添加了对控制面板自己的 SSL 的 Let's Encrypt 支持。
- 允许对入站和出站邮件服务使用每个域的 SSL 证书。
- 整合模板结构，删除超过 50% 的重复代码。
- 重新组织域配置文件的文件系统结构。
- 添加了通过用户界面和命令行更改发布分支的功能。
- v-sys-update-hestia-git：添加了从命令行使用 Git 进行更新的功能。
- 实现了对 SFTP chroot Jail 的支持。
- 新设计的用户界面，其特点是：
  - 更柔和的调色板，更好地匹配 Hestia 控制面板徽标颜色。
  - 域和其他信息的综合概述。
  - 改进的导航路径使查找更容易。
  - 改进了从移动设备查看控制面板界面时的兼容性。
- 改进了邮件域 DNS 区域值的处理。
- 在启用 SSL 的 Web 域上启用 OCSP 装订。
- v-change-web-domain-hsts：启用了对 SSL 上的 HTTP 严格传输安全 (HSTS) 的支持。
- 改进了新安装和升级期间的日志记录和控制台输出。

### Bug修复

- 修复了 HTTP 到 HTTPS 重定向的问题。
- 修复了使用 HTTPS 浏览到未启用 SSL 的域时会加载另一个网站的问题。

## [1.0.0-190618] - 2019-06-25

### 特征

-

### Bug修复

-

## [0.9.8-28] - 2019-05-16

### 特征

- 为网络域实施强制 SSL 功能。

### Bug修复

-

[1.0.4]: https://github.com/hestiacp/hestiacp/releases/tag/1.0.4
[1.0.3]: https://github.com/hestiacp/hestiacp/releases/tag/1.0.3
[1.0.1]: https://github.com/hestiacp/hestiacp/releases/tag/1.0.1
[1.0.0-190618]: https://github.com/hestiacp/hestiacp/releases/tag/1.0.0-190618
[0.9.8-28]: https://github.com/hestiacp/hestiacp/releases/tag/0.9.8-28
