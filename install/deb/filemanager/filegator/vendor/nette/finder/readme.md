Nette Finder: Files Searching
=============================

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/finder.svg)](https://packagist.org/packages/nette/finder)
[![Tests](https://github.com/nette/finder/workflows/Tests/badge.svg?branch=master)](https://github.com/nette/finder/actions)
[![Coverage Status](https://coveralls.io/repos/github/nette/finder/badge.svg?branch=master)](https://coveralls.io/github/nette/finder?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nette/finder/v/stable)](https://github.com/nette/finder/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/finder/blob/master/license.md)


Introduction
------------

Nette Finder makes browsing the directory structure really easy.

Documentation can be found on the [website](https://doc.nette.org/finder).


[Support Me](https://github.com/sponsors/dg)
--------------------------------------------

Do you like Nette Finder? Are you looking forward to the new features?

[![Buy me a coffee](https://files.nette.org/icons/donation-3.svg)](https://github.com/sponsors/dg)

Thank you!


Installation
------------

```shell
composer require nette/finder
```

All examples assume the following class alias is defined:

```php
use Nette\Utils\Finder;
```


Searching for Files
-------------------

How to find all `*.txt` files in `$dir` directory and all its subdirectories?

```php
foreach (Finder::findFiles('*.txt')->from($dir) as $key => $file) {
	// $key is a string containing absolute filename with path
	// $file is an instance of SplFileInfo
}
```

The files in the `$file` variable are instances of the `SplFileInfo` class.

If the directory does not exist, an `Nette\UnexpectedValueException` is thrown.

And what about searching for files in a directory without subdirectories? Instead of `from()` use `in()`:

```php
Finder::findFiles('*.txt')->in($dir)
```

Search by multiple masks and even multiple directories at once:

```php
Finder::findFiles('*.txt', '*.php')
	->in($dir1, $dir2) // or from($dir1, $dir2)
```

Depth of search can be limited using the `limitDepth()` method.


Searching for Directories
-------------------------

In addition to files, it is possible to search for directories using `Finder::findDirectories('subdir*')`.

Or to search for files and directories together using `Finder::find('*.txt')`, the mask in this case only applies to files. When searching recursively with `from()`, the subdirectory is returned first, followed by the files in it, which can be reversed with `childFirst()`.


Mask
----

The mask does not have to describe only the file name, but also the path. Example: searching for `*.jpg` files located in a subdirectory starting with `imag`:

```php
Finder::findFiles('imag*/*.jpg')
```

Thus, the known wildcards `*` and `?` represent any characters except the directory separator `/`. The double `**` represents any characters, including the directory separator:

```php
Finder::findFiles('imag**/*.jpg')
// finds also image/subdir/file.jpg
```

In addition you can use in the mask ranges `[...]` or negative ranges `[!...]` known from regular expressions. Searching for `*.txt` files containing a digit in the name:

```php
Finder::findFiles('*[0-9]*.txt')
```


Excluding
---------

Use `exclude()` to pass masks that the file must not match. Searching for `*.txt` files, except those containing '`X`' in the name:

```php
Finder::findFiles('*.txt')
	->exclude('*X*')
```

If `exclude()` is specified **after** `from()`, it applies to crawled subdirectories:

```php
Finder::findFiles('*.php')
	->from($dir)
	->exclude('temp', '.git')
```



Filtering
---------

You can also filter the results, for example by file size. Here's how to find files of size between 100 and 200 bytes:

```php
Finder::findFiles('*.php')
	->size('>=', 100)
	->size('<=', 200)
	->from($dir)
```

Filtering by date of last change. Example: searching for files changed in the last two weeks:

```php
Finder::findFiles('*.php')
	->date('>', '- 2 weeks')
	->from($dir)
```

Both functions understand the operators `>`, `>=`, `<`, `<=`, `=`, `!=`.

Here we traverse PHP files with number of lines greater than 1000. As a filter we use a custom callback:

```php
$hasMoreThan100Lines = function (SplFileInfo $file): bool {
	return count(file($file->getPathname())) > 1000;
};

Finder::findFiles('*.php')
	->filter($hasMoreThan100Lines)
```

Handy, right? You will certainly find a use for Finder in your applications.
