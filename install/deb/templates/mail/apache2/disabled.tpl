<VirtualHost%<i4 %web_ipv4%:%web_port%i4>%%<i6 %web_ipv6%:%web_port%i6>%>
    ServerName %domain_idn%
    ServerAlias %alias_idn%
    DocumentRoot /var/www/html/
    Alias /error/ /var/www/document_errors/
    #SuexecUserGroup %user% %group%
</VirtualHost>
