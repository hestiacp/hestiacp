# Flysystem Adapter for SFTP

[![Author](http://img.shields.io/badge/author-@frankdejonge-blue.svg?style=flat-square)](https://twitter.com/frankdejonge)
[![Build Status](https://img.shields.io/travis/thephpleague/flysystem-sftp/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/flysystem-sftp)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/thephpleague/flysystem-sftp.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/flysystem-sftp/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/thephpleague/flysystem-sftp.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/flysystem-sftp)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/league/flysystem-sftp.svg?style=flat-square)](https://packagist.org/packages/league/flysystem-sftp)
[![Total Downloads](https://img.shields.io/packagist/dt/league/flysystem-sftp.svg?style=flat-square)](https://packagist.org/packages/league/flysystem-sftp)
[![Documentation](https://img.shields.io/badge/read-documentation-brightgreen.svg)](https://flysystem.thephpleague.com/adapter/sftp/)

This adapter uses phpseclib to provide a SFTP adapter for Flysystem.

## Installation

```bash
composer require league/flysystem-sftp
```

## Documentation

Full documentation of this adapter can be found [here](https://flysystem.thephpleague.com/adapter/sftp/): https://flysystem.thephpleague.com/adapter/sftp/

## Usage

```php
use League\Flysystem\Sftp\SftpAdapter;
use League\Flysystem\Filesystem;

$adapter = new SftpAdapter([
    'host' => 'example.com',
    'port' => 22,
    'username' => 'username',
    'password' => 'password',
    'privateKey' => 'path/to/or/contents/of/privatekey',
    'passphrase' => 'passphrase-for-privateKey',
    'root' => '/path/to/root',
    'timeout' => 10,
    'directoryPerm' => 0755
]);

$filesystem = new Filesystem($adapter);
```
