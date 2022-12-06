# Troubleshooting

## Disabling “Use IP address allow list for login attempts” via command line

With the introduction of Hestia v1.4.0 we have added certain security features, including the possibility to limit login to certain IP addresses. If your IP address changes, you will not able to login anymore. To disable this feature, run the following commands:

```bash
# Disable the feature
v-change-user-config-value admin LOGIN_USE_IPLIST 'no'
# Remove listed IP addresses
v-change-user-config-value admin LOGIN_ALLOW_IPS ''
```

## Can I update my cronjobs via `crontab -e`?

No, you cannot. When you update HestiaCP, the crontab will simply get overwritten. The changes will not get saved in backups either.
