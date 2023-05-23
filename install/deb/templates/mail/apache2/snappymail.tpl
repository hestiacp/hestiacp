<VirtualHost %ip%:%web_port%>
    ServerName %domain_idn%
    ServerAlias %alias_idn%
    Alias / /var/lib/snappymail/
    Alias /error/ %home%/%user%/web/%root_domain%/document_errors/
    #SuexecUserGroup %user% %group%

    IncludeOptional %home%/%user%/conf/mail/%root_domain%/apache2.forcessl.conf*

    <Directory /var/lib/snappymail/>
        Options +FollowSymLinks
        # This is needed to parse /var/lib/snappymail/.htaccess. See its
        # content before setting AllowOverride to None.
        AllowOverride All
        order allow,deny
        allow from all
    </Directory>

    # Protecting basic directories:
    <Directory /var/lib/snappymail/data>
            Options -FollowSymLinks
            AllowOverride None
    </Directory>
    IncludeOptional %home%/%user%/conf/mail/%root_domain%/%web_system%.conf_*
</VirtualHost>
