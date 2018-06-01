;[%backend%]
;listen = /dev/null

;user = %user%
;group = %user%

;listen.owner = %user%
;listen.group = www-data

;pm = ondemand
;pm.max_children = 4
;pm.max_requests = 4000
;pm.process_idle_timeout = 10s
;pm.status_path = /status

;env[HOSTNAME] = $HOSTNAME
;env[PATH] = /usr/local/bin:/usr/bin:/bin
;env[TMP] = /home/%user%/tmp
;env[TMPDIR] = /home/%user%/tmp
;env[TEMP] = /home/%user%/tmp
