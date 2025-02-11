# Account

To access your account settings, click the <i class="fas fa-lg fa-fw fa-user-circle"><span class="visually-hidden">user</span></i> button in the top right.

## Security

### Password

The password requirements are as follows:

- At least 8 characters, 14 or more are recommended.
- At least 1 number.
- At least 1 capital letter and 1 lowercase letter.

If you want to generate a secure password, you can use [1Password’s generator](https://1password.com/password-generator/).

### Two-factor authentication (2FA)

1. In your account settings, check the box labeled **Enable two-factor authentication**.
2. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.
3. Scan the QR code using an authentication app.
4. Save your **Account Recovery Code** somewhere safe, in case you lose access to your authenticator.

::: details Need a 2FA app?
If you do not have an authenticator app, here are a couple of recommendations.

- iCloud Keychain – [Windows](https://9to5mac.com/2022/07/25/icloud-passwords-windows-2fa-code/), [macOS](https://9to5mac.com/2021/11/16/use-safari-password-manager-and-2fa-autofill/), [iOS](https://9to5mac.com/2022/03/07/use-ios-15-2fa-code-generator-plus-autofill-iphone/)
- [Tofu Authenticator](https://www.tofuauth.com/) – Open-source, iOS only
- [Aegis Authenticator](https://getaegis.app/) – Open-source, Android only
- [Google Authenticator](https://support.google.com/accounts/answer/1066447?hl=en&co=GENIE.Platform%3DAndroid)
- [Microsoft Authenticator](https://www.microsoft.com/en-ca/security/mobile-authenticator-app)
- [1Password](https://1password.com/) – Paid password manager
- [Bitwarden](https://bitwarden.com/) – Password manager. 2FA in premium plan only
- [Vaultwarden](https://docs.cloudron.io/apps/vaultwarden) (AGPL, [self-hosted](https://hub.docker.com/r/vaultwarden/server)), optionally with [Bitwarden](https://linuxiac.com/how-to-install-vaultwarden-password-manager-with-docker) clients
- [FreeOTP+](https://github.com/helloworld1/FreeOTPPlus) - Open Source, Android only [F-Droid](https://f-droid.org/en/packages/org.liberty.android.freeotpplus/)

:::

### Login restrictions

Hestia has the following options to help you secure your account:

- Disable login into the account.
- Whitelist your IP address to login into your account.

### Security logs

The security logs contain various information, such as: changes to web domains, API access, backup creation, etc. Click the **<i class="fas fa-fw fa-history"></i> Logs** button to view them.

### Login history

On the security logs page, click the **<i class="fas fa-fw fa-binoculars"></i> Login history** button to see the login history. The history contains the time of login, IP address, and user agent of the browser that was used.

## SSH keys

Click the **<i class="fas fa-fw fa-key"></i> Manage SSH keys** button to view the installed keys.

### Adding an SSH key

1. Click the **<i class="fas fa-fw fa-plus-circle"></i> Add SSH key** button.
2. Copy your public key in the text area.
3. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

### Deleting an SSH key

1. Hover over the SSH key you want to delete.
2. Click the <i class="fas fa-fw fa-trash"><span class="visually-hidden">delete</span></i> icon on the right of the SSH ID.

::: details Need to generate a key?
If you do not have an SSH key, here are a couple of ways to generate one.

- For Windows: [Putty](https://www.ssh.com/academy/ssh/putty/windows/puttygen#running-puttygen).
- For macOS and Linux use `ssh-keygen`.

You can also use an app to manage them:

- [1Password](https://developer.1password.com/docs/ssh/manage-keys/)
- [Termius](https://www.termius.com/)

:::

## API access keys

::: info
This option is disabled by default for standard users. An administrator needs to enable it in the server settings.
:::

Click the **<i class="fas fa-fw fa-key"></i> Access Keys** button to view the access keys. Access keys are used for the API to autenticate instead of using the username and password.

### Creating an access key

1. Click the **<i class="fas fa-fw fa-plus-circle"></i> Add Access key** button.
2. Select the permission sets you want to enable.
3. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.
4. Copy the access key and the secret key. Make sure to save the secret key somewhere safe as it **cannot** be viewed once the page is closed.

### Deleting an access key

1. Hover over the access key you want to delete.
2. Click the <i class="fas fa-fw fa-trash"><span class="visually-hidden">delete</span></i> icon on the right of the access key.
