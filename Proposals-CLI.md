# Proposals for HestiaCP

## Command line interface

### hestia command

A new command line interface for Hestia, with backward compatibility, using multi level commands, from general to particular, and named parameters. Syntax examples:

```bash
hestia ver                  # one level
hestia module install       # two levels (module -> install)
hestia web domain list      # three levels (web -> domain -> list)
                            # etc.

hestia user create --realname 'John Doe' --email john@hestiacp.com --force
                            # named parameters: --realname and --email have value
                            # --force is boolean (no value, just present/absent)
```

#### Reference implementation

The proposed implementation is very simple (~100 lines of code). See `bin/hestia` and try the command `hestia web domain list`. This it what happens:

* Hesia CLI will look for one the follwing files in the $HESTIA folder:

```
bin/web
bin/web/domain
bin/web/domain/list
```

* If found, Hesia CLI will execute the file and pass the remaining parameters as arguments for it.
* Additionally, Hestia CLI will parse named parameters and fill a set of variables for easier access.

##### Further elaboration

Alternatively, if `bin/web/domain/list` does not exist, but `bin/web/domain/list.inc` does, Hesia CLI will source the file and expect a function named hestia_web_domain_list() to defined. This function will be called passing all the remaining arguments as parameters.

This has the theoretical advantage that the function can start running immediately, instead of having to initialize again (source main.sh again, etc.).

Note that it's possible and easy to make a file like `bin/web/domain/list.inc` work as both include file and executable file, so this two commands are equivalent:

```bash
hestia web domain list arg1 arg2
bin/web/domain/list.inc arg1 arg2
```

It's left to decide wether either of this aproaches is best or both. Look at bin/web/domain/list.inc for an example of how this can be implemented.

##### Example

```bash
hestia user create --realname 'John Doe' --email john@hestiacp.com --force
```

will search for `bin/user/create` or `bin/user/create.sh.inc` and -if found- execute it or call `hestia_user_create()` with the following variables defined:

Variable | Value
-------- | -----
param_realname | John Doe
param_email | john@hestiacp.com
param_force | 1

##### Modularity and extensibility

As you can see, this is hierarchical, modular and extensible.

New Hestia commands can be easily added and modules (even third party) can enable new Hestia commands that seamlessly integrate by just dropping files in the bin folder.

Also, stubs can be in place for modules that are not installed.

##### Backward compatibility

BC will be very easy as old v-commands can be wrappers for new Hestia CLI commands and vice-versa.

##### File list

The list of files for the reference implementation and examples in this document:

```bash
bin/hestia                  # hestia command (main file)
bin/module/install          # command example using regular executable file
bin/web/domain/list.inc     # command example that works both as include file and executable
bin/ver.inc                 # one-level example
```