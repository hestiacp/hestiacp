#!/bin/bash
# Add notifications

rm -f /usr/local/vesta/data/users/admin/notifications.conf
/usr/local/vesta/bin/v-add-user-notification admin "File Manager" "Browse, coppy, edit, view, and retrieve all of your web domain files using fully featured <a href='http://vestacp.com/features/#filemanager'>File Manager</a>. Plugin is avaiable for <a href='/edit/server/?lead=filemanager#module-filemanager'>purchase</a>." 'filemanager'
/usr/local/vesta/bin/v-add-user-notification admin "Chroot SFTP" "If you want have SFTP accounts that will be used only to transfer files (and not to ssh), you can  <a href='/edit/server/?lead=sftp#module-sftp'>purchase</a> and enable <a href='http://vestacp.com/features/#sftpchroot'>SFTP Chroot</a>"
/usr/local/vesta/bin/v-add-user-notification admin "Free SSL Certificates" "Lets Encrypt is a free, automated, and open Certificate Authority. Find out more at  <a href='https://letsencrypt.org/'>letsencrypt.org</a>"
/usr/local/vesta/bin/v-add-user-notification admin "Keyboard Control" "You can use your keyboard to perform many of the same actions you perform using the mouse, such as navigating to or selecting menus, and items"
/usr/local/vesta/bin/v-add-user-notification admin "Release 0.9.8-17" "Notable in this release is the improved server tab. You can now edit service configs, php.ini and so on. We added nice mail helpers and fixed a dozen bugs. For more information please read the <a href='http://vestacp.com/roadmap/#0.9.8-17'>release notes</a>"
