# Return codes

| Value | Name          | Comment                                                      |
| ----- | ------------- | ------------------------------------------------------------ |
| 0     | OK            | Command has been successfully performed                      |
| 1     | E_ARGS        | Not enough arguments provided                                |
| 2     | E_INVALID     | Object or argument is not valid                              |
| 3     | E_NOTEXIST    | Object doesn’t exist                                         |
| 4     | E_EXISTS      | Object already exists                                        |
| 5     | E_SUSPENDED   | Object is already suspended                                  |
| 6     | E_UNSUSPENDED | Object is already unsuspended                                |
| 7     | E_INUSE       | Object can’t be deleted because it is used by another object |
| 8     | E_LIMIT       | Object cannot be created because of hosting package limits   |
| 9     | E_PASSWORD    | Wrong / Invalid password                                     |
| 10    | E_FORBIDEN    | Object cannot be accessed by this user                       |
| 11    | E_DISABLED    | Subsystem is disabled                                        |
| 12    | E_PARSING     | Configuration is broken                                      |
| 13    | E_DISK        | Not enough disk space to complete the action                 |
| 14    | E_LA          | Server is to busy to complete the action                     |
| 15    | E_CONNECT     | Connection failed. Host is unreachable                       |
| 16    | E_FTP         | FTP server is not responding                                 |
| 17    | E_DB          | Database server is not responding                            |
| 18    | E_RDD         | RRDtool failed to update the database                        |
| 19    | E_UPDATE      | Update operation failed                                      |
| 20    | E_RESTART     | Service restart failed                                       |
