#=========================================================================#
# Default Web Domain Template                                             #
# DO NOT MODIFY THIS FILE! CHANGES WILL BE LOST WHEN REBUILDING DOMAINS   #
# https://hestiacp.com/docs/server-administration/web-templates.html      #
#=========================================================================#

<VirtualHost%<i4 %web_ipv4%:%web_port%i4>%%<i6 %web_ipv6%:%web_port%i6>%>

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

    <Directory %docroot%>
        AllowOverride All
        Options +Includes -Indexes +ExecCGI
        php_admin_value open_basedir %docroot%:%home%/%user%/tmp
        php_admin_value upload_tmp_dir %home%/%user%/tmp
        php_admin_value session.save_path %home%/%user%/tmp
        php_admin_value sys_temp_dir %home%/%user%/tmp
    </Directory>
    <Directory %home%/%user%/web/%domain%/stats>
        AllowOverride All
    </Directory>

    <IfModule mod_ruid2.c>
        RMode config
        RUidGid %user% %group%
        RGroups www-data
    </IfModule>
    <IfModule mpm_itk.c>
        AssignUserID %user% %group%
    </IfModule>
	<IfModule mod_remoteip.c>
        RemoteIPHeader X-Real-IP
        RemoteIPInternalProxy 127.0.0.1
        RemoteIPInternalProxy %web_ipv4%
        RemoteIPInternalProxy %web_ipv6%
    </IfModule>

    IncludeOptional %home%/%user%/conf/web/%domain%/%web_system%.conf_*
    IncludeOptional /etc/apache2/conf.d/*.inc
</VirtualHost>
