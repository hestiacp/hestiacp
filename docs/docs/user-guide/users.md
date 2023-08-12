# Users

To manage users, log in as an **administrator** and navigate to the **Users <i class="fas fa-fw fa-users"></i>** tab.

## Adding a user

1. Click the **<i class="fas fa-fw fa-plus-circle"></i> Add User** button.
2. Fill out the fields.
3. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

## Impersonating a user

1. Hover over the user you want to login as.
2. Click the <i class="fas fa-fw fa-sign-in-alt"><span class="visually-hidden">login as</span></i> icon on the right of the user’s name and email.
3. You are now logged in as the user. As such, any action you perform will be done as this user.

## Editing a user

The settings specified below are only available to administrators. For the regular settings, you can refer to the [Account Management](../user-guide/account.md) documentation.

To edit a user you can either impersonate them and click the <i class="fas fa-lg fa-fw fa-user-circle"><span class="visually-hidden">user</span></i> icon in the top right, or follow these steps:

1. Hover over the user you want to edit.
2. Click the <i class="fas fa-fw fa-pencil-alt"><span class="visually-hidden">edit</span></i> icon on the right of the user’s name and email.

## Suspending a user

1. Hover over the user you want to suspend.
2. Click the <i class="fas fa-fw fa-pause"><span class="visually-hidden">suspend</span></i> icon on the right of the user’s name and email.

## Deleting a user

1. Hover over the user you want to delete.
2. Click the <i class="fas fa-fw fa-trash"><span class="visually-hidden">delete</span></i> icon on the right of the user’s name and email.

## User configuration

### Disabling control panel access

To remove Control Panel access from a user, check the box labelled: **Do not allow user to log in to Control Panel**.

### Changing role

To change a user’s role change the **Role** value from the dropdown.

::: warning
Assigning the **Administrator** role to a user will enable them to see and edit other users. They will not be able to edit the **admin** user, but will be able to see them, unless disabled in the server settings.
:::

### Changing package

To change a user’s package, change the **Package** value from the dropdown.

### Changing SSH access

To change a user’s SSH access, click the **Advanced Options** button, then change the **SSH Access** value from the dropdown.

::: warning
Using the **nologin** shell will _not_ disable SFTP access.
:::

### Changing PHP CLI version

To change a user’s PHP CLI version, click the **Advanced Options** button, then change the **PHP CLI Version** value from the dropdown.

### Changing default name servers

To change a user’s default name servers, click the **Advanced Options** button, then edit the **Default Name Servers** fields.

::: warning
At least 2 default name servers are necessary. This is to provide redundancy, in case one of them fails to answer. In fact, it is suggested that both name servers be on separate servers, for better resilience. If you are the system administrator and would like to set this up, refer to our [DNS Cluster documentation](../server-administration/dns.md#dns-cluster-setup).
:::
