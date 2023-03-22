#=========================================================================#
# Default Web Domain Template                                            #

# DO NOT MODIFY THIS FILE! CHANGES WILL BE LOST WHEN REBUILDING DOMAINS   #
# https://docs.hestiacp.com/admin_docs/web.html#how-do-web-templates-work #
#=========================================================================#

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
        
    IncludeOptional %home%/%user%/conf/web/%domain%/forcessl.apache2.conf*
    
    <IfModule mod_php5.c>
        Define PHP_ENABLED
    </IfModule>
    <IfModule mod_php7.c>
        Define PHP_ENABLED
    </IfModule>
    <Directory %docroot%>
        AllowOverride All
        Options +Includes -Indexes +ExecCGI
        <IfDefine PHP_ENABLED>
            php_admin_value upload_max_filesize 10M
            php_admin_value max_execution_time 20
            php_admin_value post_max_size  8M
            php_admin_value memory_limit 32M
            php_admin_flag mysql.allow_persistent  off
            php_admin_flag safe_mode off
            php_admin_value sendmail_path "/usr/sbin/sendmail -t -i -f info@%domain_idn%"
            php_admin_value open_basedir %docroot%:%home%/%user%/tmp:/bin:/usr/bin:/usr/local/bin:/var/www/html:/tmp:/usr/share:/etc/phpMyAdmin:/etc/phpmyadmin:/var/lib/phpmyadmin:/etc/roundcubemail:/etc/roundcube:/var/lib/roundcube
            php_admin_value upload_tmp_dir %home%/%user%/tmp
            php_admin_value session.save_path %home%/%user%/tmp
            php_admin_value sys_temp_dir %home%/%user%/tmp
        </IfDefine>
    </Directory>
    <Directory %home%/%user%/web/%domain%/stats>
        AllowOverride All
    </Directory>
    <IfModule mpm_itk.c>
        AssignUserID %user% %group%
    </IfModule>

    IncludeOptional %home%/%user%/conf/web/%domain%/%web_system%.conf_*

</VirtualHost>

