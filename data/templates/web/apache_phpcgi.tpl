<VirtualHost %ip%:%web_port%>

    ServerName %domain_idn%
    %alias_string%
    ServerAdmin %email%
    %docroot_string%
    %cgi%ScriptAlias /cgi-bin/ %home%/%user%/web/%domain%/cgi-bin/
    Alias /vstats/ %home%/%user%/web/%domain%/stats/
    Alias /error/ %home%/%user%/web/%domain%/document_errors/
    SuexecUserGroup %user% %group%
    CustomLog /var/log/httpd/domains/%domain%.bytes bytes
    CustomLog /var/log/httpd/domains/%domain%.log combined
    %elog%ErrorLog /var/log/httpd/domains/%domain%.error.log
    <Directory %docroot%>
        AllowOverride AuthConfig FileInfo Indexes Limit
        Options +Includes -Indexes %cgi_option%
        php_admin_flag engine off
        Action phpcgi-script /cgi-bin/php
        AddHandler phpcgi-script .php
    </Directory>
    <Directory %home%/%user%/web/%domain%/stats>
        AllowOverride All
    </Directory>
    Include %home%/%user%/conf/httpd.%domain%.conf*

</VirtualHost>

