# Proposals for HestiaCP

## General considerations

* Move variable data into `/var` or `/etc`. Symlink so things don't break.
* In general, software should act as if `/usr` is read-only (which sometimes is). Only modifiable during package installation.
* Use wrappers and variables to make code distro-independent (see OSAL). This also makes code easeier to read and mantain and less prone to errors.