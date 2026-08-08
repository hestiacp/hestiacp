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

    SetEnvIf Authorization .+ HTTP_AUTHORIZATION=$0

    ProxyPass / https://localhost:8000
    ProxyPassReverse / https://localhost:8000
    ProxyPass /admin http://localhost:8000/admin
    ProxyPassReverse /admin http://localhost:8000/admin
    ProxyPass /static http://localhost:8000/static

    IncludeOptional %home%/%user%/conf/web/%domain%/%web_system%.conf_*

</VirtualHost>
