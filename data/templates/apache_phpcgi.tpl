<VirtualHost %ip%:%port%>

    ServerName %domain_idn%
    ServerAlias %alias_idn%
    ServerAdmin %email%
    DocumentRoot %docroot%
    ScriptAlias /cgi-bin/ %home%/%user%/domains/%domain%/cgi-bin/
    Alias /vstats/ %home%/%user%/domains/%domain%/stats/
    Alias /error/ %home%/%user%/domains/%domain%/document_errors/
    SuexecUserGroup %user% %group%
    CustomLog /var/log/httpd/domains/%domain%.bytes bytes
    CustomLog /var/log/httpd/domains/%domain%.log combined
    #ErrorLog /var/log/httpd/domains/%domain%.error.log
    <Directory %docroot%>
        AllowOverride AuthConfig FileInfo Indexes Limit
        Options +Includes -Indexes +ExecCGI

        php_admin_flag engine off

        Action phpcgi-script /cgi-bin/php
        AddHandler phpcgi-script .php

    </Directory>

    <Directory %home%/%user%/domains/%domain%/stats>
        AllowOverride All
    </Directory>

</VirtualHost>

