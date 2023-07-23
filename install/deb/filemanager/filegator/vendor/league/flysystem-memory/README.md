# Flysystem Memory Adapter

[![Author](http://img.shields.io/badge/author-@chrisleppanen-blue.svg?style=flat-square)](https://twitter.com/chrisleppanen)
[![Build Status](https://img.shields.io/travis/thephpleague/flysystem-memory/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/flysystem-memory)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/thephpleague/flysystem-memory.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/flysystem-memory/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/thephpleague/flysystem-memory.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/flysystem-memory)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/league/flysystem-memory.svg?style=flat-square)](https://packagist.org/packages/league/flysystem-memory)
[![Total Downloads](https://img.shields.io/packagist/dt/league/flysystem-memory.svg?style=flat-square)](https://packagist.org/packages/league/flysystem-memory)

This adapter keeps the filesystem in memory. It's useful when you need a filesystem, but do not need it persisted.

## Installation

```bash
composer require league/flysystem-memory
```

## Usage

```php
use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;

$filesystem = new Filesystem(new MemoryAdapter());

$filesystem->write('new_file.txt', 'yay a new text file!');

$contents = $filesystem->read('new_file.txt');

// Explicitly set timestamp (e.g. for testing)
$filesystem->write('old_file.txt', 'very old content', ['timestamp' => 13377331]);
```