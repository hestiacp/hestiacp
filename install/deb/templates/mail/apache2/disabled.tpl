<VirtualHost %ip%:%web_port%>
    ServerName %domain_idn%
    ServerAlias %alias%
    Alias / /var/lib/roundcube/
    Alias /error/ %home%/%user%/web/%root_domain%/document_errors/
    #SuexecUserGroup %user% %group%
    
    SSLEngine on
    SSLVerifyClient none
    
    <Directory /var/www/html>
        Options +FollowSymLinks
        AllowOverride All
        order allow,deny
        allow from all
    </Directory>
</VirtualHost>