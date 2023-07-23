# Flysystem Adapter for ZipArchive

[![Author](http://img.shields.io/badge/author-@frankdejonge-blue.svg?style=flat-square)](https://twitter.com/frankdejonge)
[![Build Status](https://img.shields.io/travis/thephpleague/flysystem-ziparchive/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/flysystem-ziparchive)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/thephpleague/flysystem-ziparchive.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/flysystem-ziparchive/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/thephpleague/flysystem-ziparchive.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/flysystem-ziparchive)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/league/flysystem-ziparchive.svg?style=flat-square)](https://packagist.org/packages/league/flysystem-ziparchive)
[![Total Downloads](https://img.shields.io/packagist/dt/league/flysystem-ziparchive.svg?style=flat-square)](https://packagist.org/packages/league/flysystem-ziparchive)


## Installation

```bash
composer require league/flysystem-ziparchive
```

## Usage

```php
use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\ZipArchiveAdapter as Adapter;

$filesystem = new Filesystem(new Adapter(__DIR__.'/path/to/archive.zip'));
```
