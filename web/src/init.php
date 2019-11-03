<?php

declare(strict_types=1);

$loader = require_once (__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

# 
# Dev-debugging: Html error handler
# https://github.com/filp/whoops
# install:
# cd $HESTIA/web/src; composer require filp/whoops
# 
# $whoops = new \Whoops\Run;
# $whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler);
# $whoops->register();

