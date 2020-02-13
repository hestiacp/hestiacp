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
        
    IncludeOptional %home%/%user%/conf/web/%domain%/apache2.forcessl.conf*
    
    <Directory %home%/%user%/web/%domain%/stats>
        AllowOverride All
    </Directory>
    <Directory %sdocroot%>
        AllowOverride All
        Options +Includes -Indexes +ExecCGI
    </Directory>

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php5.6-fpm-%domain%.sock|fcgi://localhost"
    </FilesMatch>
    SetEnvIf Authorization .+ HTTP_AUTHORIZATION=$0

    IncludeOptional %home%/%user%/conf/web/%domain%/%web_system%.conf_*

</VirtualHost>

