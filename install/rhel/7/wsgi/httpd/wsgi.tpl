<VirtualHost %ip%:%web_port%>

    ServerName %domain_idn%
    %alias_string%
    ServerAdmin %email%
    DocumentRoot %docroot%
    ScriptAlias /cgi-bin/ %home%/%user%/web/%domain%/cgi-bin/
    Alias /vstats/ %home%/%user%/web/%domain%/stats/
    Alias /error/ %home%/%user%/web/%domain%/document_errors/
    SuexecUserGroup %user% %group%
    CustomLog /var/log/%web_system%/domains/%domain%.bytes bytes
    CustomLog /var/log/%web_system%/domains/%domain%.log combined
    ErrorLog /var/log/%web_system%/domains/%domain%.error.log
    <Directory %home%/%user%/web/%domain%/stats>
        AllowOverride All
    </Directory>

    <IfModule mod_ruid2.c>
        RMode config
        RUidGid %user% %group%
        RGroups apache
    </IfModule>
    <IfModule itk.c>
        AssignUserID %user% %group%
    </IfModule>
    <IfModule mod_wsgi.c>
        WSGIDaemonProcess apx-idea user=%user% group=%user% processes=1 threads=5 display-name=%{GROUP} python-path=%home%/%user%/web/%domain%/private/django/%domain%/env/lib/python2.6/site-packages
        WSGIProcessGroup apx-idea
        WSGIApplicationGroup %{GLOBAL}
    </IfModule>

    <Directory %docroot%>
        AllowOverride FileInfo
        Options ExecCGI Indexes
        MultiviewsMatch Handlers
        Options +FollowSymLinks
        Order allow,deny
        Allow from all
    </Directory>

    Include %home%/%user%/conf/web/%web_system%.%domain_idn%.conf*

</VirtualHost>

