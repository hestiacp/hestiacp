
#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.4.4

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

#Add nginx user_agent separation to desktop/mobile
cp -f $HESTIA_INSTALL_DIR/nginx/agents.conf /etc/nginx/conf.d/
