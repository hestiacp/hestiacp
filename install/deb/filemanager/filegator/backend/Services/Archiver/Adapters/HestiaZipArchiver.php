<?php

namespace Filegator\Services\Archiver\Adapters;

use Filegator\Container\Container;
use Filegator\Services\Archiver\ArchiverInterface;
use Filegator\Services\Service;
use Filegator\Services\Storage\Filesystem as Storage;
use Filegator\Services\Tmpfs\TmpfsInterface;
use function Hestiacp\quoteshellarg\quoteshellarg;

class HestiaZipArchiver extends ZipArchiver implements Service, ArchiverInterface {
	protected $container;

	public function __construct(TmpfsInterface $tmpfs, Container $container) {
		$this->tmpfs = $tmpfs;
		$this->container = $container;
	}

	public function uncompress(string $source, string $destination, Storage $storage) {
		$auth = $this->container->get("Filegator\Services\Auth\AuthInterface");

		$v_user = basename($auth->user()->getUsername());

		if (!strlen($v_user)) {
			return;
		}

		$base = "/home/$v_user";

		if (!str_starts_with($source, "/home")) {
			$source = "$base/" . $source;
		}
		if (!str_starts_with($destination, "/home")) {
			$destination = "$base/" . $destination;
		}

		$real_source = realpath($source);
		$real_dest = realpath($destination);

		if ($real_source === false || !str_starts_with($real_source, $base)) {
			return;
		}
		if ($real_dest === false || !str_starts_with($real_dest, $base)) {
			return;
		}

		exec(
			"sudo /usr/local/hestia/bin/v-extract-fs-archive " .
				quoteshellarg($v_user) .
				" " .
				quoteshellarg($real_source) .
				" " .
				quoteshellarg($real_dest),
			$output,
			$return_var,
		);
	}
}
