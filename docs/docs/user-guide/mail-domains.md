# Mail Domains

To manage your mail domains, navigate to the **Mail <i class="fas fa-fw fa-mail-bulk"></i>** tab.

## Adding a mail domain

1. Click the **<i class="fas fa-fw fa-plus-circle"></i> Add Mail domain** button.
2. Enter your domain name.
3. Select the options you want to use.
4. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

## Editing a mail domain

1. Hover over the domain you want to edit.
2. Click the <i class="fas fa-fw fa-pencil-alt"><span class="visually-hidden">edit</span></i> icon on the right of the mail domain.
3. Edit the fields.
4. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

## Suspending a web domain

1. Hover over the domain you want to suspend.
2. Click the <i class="fas fa-fw fa-pause"><span class="visually-hidden">suspend</span></i> icon on the right of the mail domain.
3. To unsuspend it, click the <i class="fas fa-fw fa-play"><span class="visually-hidden">unsuspend</span></i> icon on the right of the mail domain.

## Deleting a web domain

1. Hover over the domain you want to delete.
2. Click the <i class="fas fa-fw fa-trash"><span class="visually-hidden">delete</span></i> icon on the right of the mail domain. Both the mail domain and **all** the mail accounts will get deleted.

## Mail domain configuration

### Webmail client

We currently support Roundcube, Rainloop and SnappyMail (optional install). You can also disable webmail access.

### Catch all email

This email address will receive all emails for this domain that are sent to users that don’t exist.

### Rate limit

::: info
This option is only available for the admin user.
:::

Set the limit for the amount of emails an account can send per hour.

### Spam filter

::: info
This option is not always available.
:::

Enable Spam Assassin for this domain.

### Antivirus

::: info
This option is not always available
:::

Enable ClamAV for this domain.

### DKIM

Enable DKIM for this domain.

### SSL

1. Check the **Enable SSL for this domain** box.
2. Check the **Use Let’s Encrypt to obtain SSL certificate** box to use Let’s Encrypt.
3. Depending on your requirements, you can enable **Enable automatic HTTPS redirection** or **Enable HTTP Strict Transport Security (HSTS)**.
4. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

If you want to use your own SSL certificate you can enter the SSL certificate in the text area.

If you are having issues with enabling Let’s Encrypt, please refer to our [SSL certificates](../server-administration/ssl-certificates.md) documentation.

### SMTP relay

This option allows the user to use a different SMTP relay than server defined one or bypass the default Exim route. This can enhance deliverability.

1. Check the **SMTP Relay** box and a form will appear.
2. Enter the information from your SMTP relay provider.

### Get DNS records

If you don’t host your DNS in Hestia, but you still want to use its email service, click the <i class="fas fa-atlas"><span class="visually-hidden">DNS</span></i> icon to view the DNS records you need to add to your DNS provider.

### Webmail

By default, the webmail is accessible at `https://webmail.domain.tld` or `https://mail.domain.tld` when SSL is enabled. Otherwise use `http://` instead.

## Adding a mail account to a domain

1. Click the mail domain.
2. Click **<i class="fas fa-fw fa-plus-circle"></i> Add Mail account** button.
3. Enter the account name (without the `@domain.tld` part) and a password.
4. Optionally, supply an email address that will receive the login details.
5. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

If required you can also modify the **Advanced Options**, which are explained below.

On the right side, you can see the methods to access your mail account via SMTP, IMAP and POP3.

## Editing a mail account

1. Hover over the account you want to edit.
2. Click the <i class="fas fa-fw fa-pencil-alt"><span class="visually-hidden">edit</span></i> icon on the right of the mail account.
3. Edit the fields.
4. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

## Suspending a mail account

1. Hover over the account you want to suspend.
2. Click the <i class="fas fa-fw fa-pause"><span class="visually-hidden">suspend</span></i> icon on the right of the mail account.
3. To unsuspend it, click the <i class="fas fa-fw fa-play"><span class="visually-hidden">unsuspend</span></i> icon on the right of the mail account.

## Deleting a mail account

1. Hover over the account you want to delete.
2. Click the <i class="fas fa-fw fa-trash"><span class="visually-hidden">delete</span></i> icon on the right of the mail account.

## Mail account configuration

### Quota

The maximum space the account is allowed to use. This includes mail, contacts, etc.

### Aliases

Add an alias to redirect mail to the main account. Enter the username only. For example: `alice`.

### Discard all mail

All incoming mail will not get forwarded and will get deleted.

### Do not store forwarded mail

If this option is selected, all forwarded mail will get deleted.

### Auto-Reply

Setup an auto-reply.

### Forward mail

Forward all incoming mail to the entered email address.

::: warning
A lot of spam filters may flag the forwarded mail as spam by default!
:::

### Rate limit

Set the limit for the amount of emails an account can send per hour.
