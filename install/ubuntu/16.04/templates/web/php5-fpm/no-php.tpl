;[%backend%]
;user = %user%
;group = %user%
;listen = /dev/null

;listen.owner = %user%
;listen.group = nginx

;pm = dynamic
;pm.max_children = 50
;pm.start_servers = 3
;pm.min_spare_servers = 2
;pm.max_spare_servers = 10
