#!/bin/bash
# Add notifications

rm -f /usr/local/vesta/data/users/admin/notifications.conf
/usr/local/vesta/bin/v-add-user-notification admin "File Manager" "Browse, copy, edit, view, and retrieve all your web domain files using a fully featured <a href='http://vestacp.com/features/#filemanager'>File Manager</a>. Plugin is available for <a href='/edit/server/?lead=filemanager#module-filemanager'>purchase</a>." 'filemanager'
/usr/local/vesta/bin/v-add-user-notification admin "Chroot SFTP" "If you want to have SFTP accounts that will be used only to transfer files (and not to SSH), you can  <a href='/edit/server/?lead=sftp#module-sftp'>purchase</a> and enable <a href='http://vestacp.com/features/#sftpchroot'>SFTP Chroot</a>"
/usr/local/vesta/bin/v-add-user-notification admin "Release 0.9.8-18" "Biggest the <a href='http://vestacp.com/roadmap/#0.9.8-18'>release notes</a>"
