<VirtualHost%<i4 %web_ipv4%:%web_port%i4>%%<i6 %web_ipv6%:%web_port%i6>%>
    ServerName %domain_idn%
    ServerAlias %alias_idn%
    Alias / /var/lib/rainloop/
    Alias /error/ %home%/%user%/web/%root_domain%/document_errors/
    #SuexecUserGroup %user% %group%

    IncludeOptional %home%/%user%/conf/mail/%root_domain%/apache2.forcessl.conf*

    <Directory /var/lib/rainloop/>
        Options +FollowSymLinks
        # This is needed to parse /var/lib/rainloop/.htaccess. See its
        # content before setting AllowOverride to None.
        AllowOverride All
        order allow,deny
        allow from all
    </Directory>

    # Protecting basic directories:
    <Directory /var/lib/rainloop/data>
            Options -FollowSymLinks
            AllowOverride None
    </Directory>
    IncludeOptional %home%/%user%/conf/mail/%root_domain%/%web_system%.conf_*
</VirtualHost>
