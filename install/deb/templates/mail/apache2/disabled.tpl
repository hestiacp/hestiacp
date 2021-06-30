<VirtualHost %ip%:%web_port%>
    ServerName %domain_idn%
    ServerAlias %alias_idn%
    Alias / /var/www/html
    Alias /error/ %home%/%user%/web/%root_domain%/document_errors/
    #SuexecUserGroup %user% %group%
        
    IncludeOptional %home%/%user%/conf/mail/%root_domain%/apache2.forcessl.conf*

    <Directory /var/www/html>
        Options +FollowSymLinks
        AllowOverride All
        order allow,deny
        allow from all
    </Directory>

    IncludeOptional %home%/%user%/conf/mail/%root_domain%/%web_system%.conf_*
</VirtualHost>