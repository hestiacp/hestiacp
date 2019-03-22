<VirtualHost %ip%:%web_port%>

    ServerName %domain_idn%
    %alias_string%
    ServerAdmin %email%
    DocumentRoot %docroot%
    ScriptAlias /cgi-bin/ %home%/%user%/web/%domain%/cgi-bin/
    Alias /vstats/ %home%/%user%/web/%domain%/stats/
    Alias /error/ %home%/%user%/web/%domain%/document_errors/
    #SuexecUserGroup %user% %group%
    CustomLog /var/log/%web_system%/domains/%domain%.bytes bytes
    CustomLog /var/log/%web_system%/domains/%domain%.log combined
    ErrorLog /var/log/%web_system%/domains/%domain%.error.log
        
    IncludeOptional %home%/%user%/conf/web/forcessl.apache2.%domain%.conf*
    
    <Directory %docroot%>
        AllowOverride All
        Options +Includes -Indexes +ExecCGI
        php_admin_value open_basedir %docroot%:%home%/%user%/tmp
        php_admin_value upload_tmp_dir %home%/%user%/tmp
        php_admin_value session.save_path %home%/%user%/tmp
    </Directory>
    <Directory %home%/%user%/web/%domain%/stats>
        AllowOverride All
    </Directory>

    <IfModule mod_ruid2.c>
        RMode config
        RUidGid %user% %group%
        RGroups www-data
    </IfModule>
    <IfModule itk.c>
        AssignUserID %user% %group%
    </IfModule>

    IncludeOptional %home%/%user%/conf/web/%web_system%.%domain%.conf*

</VirtualHost>

<VirtualHost %ip%:%web_port%>

    ServerName mail.%domain_idn%
    ServerAdmin %email%
    DocumentRoot /var/lib/roundcube/
    Alias /error/ %home%/%user%/web/%domain%/document_errors/
    #SuexecUserGroup %user% %group%
        
    IncludeOptional %home%/%user%/conf/mail/forcessl.apache2.%domain%.conf*
    
    <Directory "/usr/share/tinymce/www/">
      Options Indexes MultiViews FollowSymLinks
      AllowOverride None
      Order allow,deny
      allow from all
    </Directory>

    <Directory /var/lib/roundcube/>
        Options +FollowSymLinks
        # This is needed to parse /var/lib/roundcube/.htaccess. See its
        # content before setting AllowOverride to None.
        AllowOverride All
        order allow,deny
        allow from all
    </Directory>

    # Protecting basic directories:
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


    <IfModule mod_ruid2.c>
        RMode config
        RUidGid %user% %group%
        RGroups www-data
    </IfModule>
    <IfModule itk.c>
        AssignUserID %user% %group%
    </IfModule>

    IncludeOptional %home%/%user%/conf/web/%web_system%.mail.%domain%.conf*

</VirtualHost>

