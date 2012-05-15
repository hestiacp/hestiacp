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
        AllowOverride All
        Options +Includes -Indexes %cgi_option%
        php_admin_value upload_tmp_dir %home%/%user%/tmp
        php_admin_value upload_max_filesize 60M
        php_admin_value max_execution_time 60
        php_admin_value post_max_size  60M
        php_admin_value memory_limit 60M
        php_admin_flag mysql.allow_persistent  off
        php_admin_flag safe_mode off
        php_admin_value session.save_path %home%/%user%/tmp
        php_admin_value sendmail_path '/usr/sbin/sendmail -t -i -f %email%'
    </Directory>
    <Directory %home%/%user%/web/%domain%/stats>
        AllowOverride All
    </Directory>
    php_admin_value open_basedir none

    <IfModule mod_ruid2.c>
        RMode config
        RUidGid %user% %group%
        RGroups apache
    </IfModule>
    <IfModule itk.c>
        AssignUserID %user% %group%
    </IfModule>

    Include %home%/%user%/conf/httpd.%domain%.conf*

</VirtualHost>

