#!/bin/bash
# Add notifications

if [ ! -e '/usr/local/vesta/data/users/admin/notifications.conf' ]; then
    /usr/local/vesta/bin/v-add-user-notification admin "File Manager" "Browse, coppy, edit, view, and retrieve all of your web domain files using fully featured <a href='http://vestacp.com/features/#filemanager'>File Manager</a>. Plugin is avaiable for <a href='/edit/server/?lead=filemanager#module-filemanager'>purchase</a>." 'filemanager'
    /usr/local/vesta/bin/v-add-user-notification admin "Chroot SFTP" "If you want have SFTP accounts that will be used only to transfer files (and not to ssh), you can  <a href='/edit/server/?lead=sftp#module-sftp'>purchase</a> and enable <a href='http://vestacp.com/features/#sftpchroot'>SFTP Chroot</a>. "
    /usr/local/vesta/bin/v-add-user-notification admin "Starred Objects" "Use stars to easily mark certain object as important or to indicate that you need to do something about it later." 'starred'
    /usr/local/vesta/bin/v-add-user-notification admin "Monitoring Tools" "This module answers common performance, health, usage and monitoring questions. <a href='/list/server/?cpu'> Server -> Show CPU, Memory, Disk usage</a>"
    /usr/local/vesta/bin/v-add-user-notification admin "Release 0.9.8-15" "New version is cool. A lot of work has been put in the user experience and stability. Check out <a href='http://vestacp.com/roadmap/#0.9.8-15'>release notes</a>"
fi
