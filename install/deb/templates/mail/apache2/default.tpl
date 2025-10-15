<VirtualHost %ip%:%web_port%>
    ServerName %domain_idn%
    ServerAlias %alias_idn%
    Alias / /var/lib/roundcube/public_html/
    Alias /error/ %home%/%user%/web/%root_domain%/document_errors/
    #SuexecUserGroup %user% %group%

    IncludeOptional %home%/%user%/conf/mail/%root_domain%/apache2.forcessl.conf*

    AcceptPathInfo On

    <Directory "/usr/share/tinymce/www/">
        Options Indexes MultiViews FollowSymLinks
        AllowOverride None
        Order allow,deny
        allow from all
    </Directory>

    <Directory /var/lib/roundcube/public_html/>
        Options +FollowSymLinks
        # This is needed to parse /var/lib/roundcube/public_html/.htaccess. See its
        # content before setting AllowOverride to None.
        AllowOverride All
        order allow,deny
        allow from all
    </Directory>

    # Protecting basic directories:
    <Directory /var/lib/roundcube>
        Options -FollowSymLinks
        AllowOverride None
    </Directory>

    <Directory /var/lib/roundcube/config>
        Options -FollowSymLinks
        AllowOverride None
    </Directory>

    <Directory /var/lib/roundcube/temp>
        Options -FollowSymLinks
        AllowOverride None
        Order allow,deny
        Deny from all
    </Directory>

    <Directory /var/lib/roundcube/logs>
        Options -FollowSymLinks
        AllowOverride None
        Order allow,deny
        Deny from all
    </Directory>

    IncludeOptional %home%/%user%/conf/mail/%root_domain%/%web_system%.conf_*

</VirtualHost>
