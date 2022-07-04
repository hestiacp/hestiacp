<?php

namespace Filegator\Services\Archiver\Adapters;

use Filegator\Container\Container;
use Filegator\Services\Archiver\ArchiverInterface;
use Filegator\Services\Service;
use Filegator\Services\Storage\Filesystem as Storage;
use Filegator\Services\Tmpfs\TmpfsInterface;
use function Divinity76\quoteshellarg\quoteshellarg;


class HestiaZipArchiver extends ZipArchiver implements Service, ArchiverInterface
{
    protected $container;

    public function __construct(TmpfsInterface $tmpfs, Container $container)
    {
        $this->tmpfs = $tmpfs;
        $this->container = $container;
    }

    public function uncompress(string $source, string $destination, Storage $storage)
    {

        $auth = $this->container->get('Filegator\Services\Auth\AuthInterface');

        $v_user = basename($auth->user()->getUsername());
        
        if(!strlen($v_user)) {
            return;
        }

        if(strpos($source, '/home') === false) {
            $source = "/home/$v_user/" . $source;
        }

        if(strpos($destination, '/home') === false) {
            $destination = "/home/$v_user/" . $destination;
        }

        exec ("sudo /usr/local/hestia/bin/v-extract-fs-archive " .
            quoteshellarg($v_user)  . " " .
            quoteshellarg($source) . " " .
            quoteshellarg($destination)
        ,$output, $return_var);

    }
}
