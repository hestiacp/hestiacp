<VirtualHost %ip%:%web_port%>
    ServerName %domain_idn%
    ServerAlias %alias_idn%
    DocumentRoot /var/www/html/
    Alias /error/ /var/www/document_errors/
    #SuexecUserGroup %user% %group%
</VirtualHost>