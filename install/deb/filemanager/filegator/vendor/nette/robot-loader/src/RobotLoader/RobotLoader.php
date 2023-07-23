<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Loaders;

use Nette;
use SplFileInfo;


/**
 * Nette auto loader is responsible for loading classes and interfaces.
 *
 * <code>
 * $loader = new Nette\Loaders\RobotLoader;
 * $loader->addDirectory('app');
 * $loader->excludeDirectory('app/exclude');
 * $loader->setTempDirectory('temp');
 * $loader->register();
 * </code>
 */
class RobotLoader
{
	use Nette\SmartObject;

	private const RetryLimit = 3;

	/** @var string[] */
	public $ignoreDirs = ['.*', '*.old', '*.bak', '*.tmp', 'temp'];

	/** @var string[] */
	public $acceptFiles = ['*.php'];

	/** @var bool */
	private $autoRebuild = true;

	/** @var bool */
	private $reportParseErrors = true;

	/** @var string[] */
	private $scanPaths = [];

	/** @var string[] */
	private $excludeDirs = [];

	/** @var array<string, array{string, int}>  class => [file, time] */
	private $classes = [];

	/** @var bool */
	private $cacheLoaded = false;

	/** @var bool */
	private $refreshed = false;

	/** @var array<string, int>  class => counter */
	private $missingClasses = [];

	/** @var array<string, int>  file => mtime */
	private $emptyFiles = [];

	/** @var string|null */
	private $tempDirectory;

	/** @var bool */
	private $needSave = false;


	public function __construct()
	{
		if (!extension_loaded('tokenizer')) {
			throw new Nette\NotSupportedException('PHP extension Tokenizer is not loaded.');
		}
	}


	public function __destruct()
	{
		if ($this->needSave) {
			$this->saveCache();
		}
	}


	/**
	 * Register autoloader.
	 */
	public function register(bool $prepend = false): self
	{
		spl_autoload_register([$this, 'tryLoad'], true, $prepend);
		return $this;
	}


	/**
	 * Handles autoloading of classes, interfaces or traits.
	 */
	public function tryLoad(string $type): void
	{
		$this->loadCache();

		$missing = $this->missingClasses[$type] ?? null;
		if ($missing >= self::RetryLimit) {
			return;
		}

		[$file, $mtime] = $this->classes[$type] ?? null;

		if ($this->autoRebuild) {
			if (!$this->refreshed) {
				if (!$file || !is_file($file)) {
					$this->refreshClasses();
					[$file] = $this->classes[$type] ?? null;
					$this->needSave = true;

				} elseif (filemtime($file) !== $mtime) {
					$this->updateFile($file);
					[$file] = $this->classes[$type] ?? null;
					$this->needSave = true;
				}
			}

			if (!$file || !is_file($file)) {
				$this->missingClasses[$type] = ++$missing;
				$this->needSave = $this->needSave || $file || ($missing <= self::RetryLimit);
				unset($this->classes[$type]);
				$file = null;
			}
		}

		if ($file) {
			(static function ($file) { require $file; })($file);
		}
	}


	/**
	 * Add path or paths to list.
	 * @param  string  ...$paths  absolute path
	 */
	public function addDirectory(...$paths): self
	{
		if (is_array($paths[0] ?? null)) {
			trigger_error(__METHOD__ . '() use variadics ...$paths to add an array of paths.', E_USER_WARNING);
			$paths = $paths[0];
		}

		$this->scanPaths = array_merge($this->scanPaths, $paths);
		return $this;
	}


	public function reportParseErrors(bool $on = true): self
	{
		$this->reportParseErrors = $on;
		return $this;
	}


	/**
	 * Excludes path or paths from list.
	 * @param  string  ...$paths  absolute path
	 */
	public function excludeDirectory(...$paths): self
	{
		if (is_array($paths[0] ?? null)) {
			trigger_error(__METHOD__ . '() use variadics ...$paths to add an array of paths.', E_USER_WARNING);
			$paths = $paths[0];
		}

		$this->excludeDirs = array_merge($this->excludeDirs, $paths);
		return $this;
	}


	/**
	 * @return array<string, string>  class => filename
	 */
	public function getIndexedClasses(): array
	{
		$this->loadCache();
		$res = [];
		foreach ($this->classes as $class => [$file]) {
			$res[$class] = $file;
		}

		return $res;
	}


	/**
	 * Rebuilds class list cache.
	 */
	public function rebuild(): void
	{
		$this->cacheLoaded = true;
		$this->classes = $this->missingClasses = $this->emptyFiles = [];
		$this->refreshClasses();
		if ($this->tempDirectory) {
			$this->saveCache();
		}
	}


	/**
	 * Refreshes class list cache.
	 */
	public function refresh(): void
	{
		$this->loadCache();
		if (!$this->refreshed) {
			$this->refreshClasses();
			$this->saveCache();
		}
	}


	/**
	 * Refreshes $this->classes & $this->emptyFiles.
	 */
	private function refreshClasses(): void
	{
		$this->refreshed = true; // prevents calling refreshClasses() or updateFile() in tryLoad()
		$files = $this->emptyFiles;
		$classes = [];
		foreach ($this->classes as $class => [$file, $mtime]) {
			$files[$file] = $mtime;
			$classes[$file][] = $class;
		}

		$this->classes = $this->emptyFiles = [];

		foreach ($this->scanPaths as $path) {
			$iterator = is_file($path)
				? [new SplFileInfo($path)]
				: $this->createFileIterator($path);

			foreach ($iterator as $fileInfo) {
				$mtime = $fileInfo->getMTime();
				$file = $fileInfo->getPathname();
				$foundClasses = isset($files[$file]) && $files[$file] === $mtime
					? ($classes[$file] ?? [])
					: $this->scanPhp($file);

				if (!$foundClasses) {
					$this->emptyFiles[$file] = $mtime;
				}

				$files[$file] = $mtime;
				$classes[$file] = []; // prevents the error when adding the same file twice

				foreach ($foundClasses as $class) {
					if (isset($this->classes[$class])) {
						throw new Nette\InvalidStateException(sprintf(
							'Ambiguous class %s resolution; defined in %s and in %s.',
							$class,
							$this->classes[$class][0],
							$file
						));
					}

					$this->classes[$class] = [$file, $mtime];
					unset($this->missingClasses[$class]);
				}
			}
		}
	}


	/**
	 * Creates an iterator scaning directory for PHP files, subdirectories and 'netterobots.txt' files.
	 * @throws Nette\IOException if path is not found
	 */
	private function createFileIterator(string $dir): Nette\Utils\Finder
	{
		if (!is_dir($dir)) {
			throw new Nette\IOException(sprintf("File or directory '%s' not found.", $dir));
		}

		$dir = realpath($dir) ?: $dir; // realpath does not work in phar

		if (is_string($ignoreDirs = $this->ignoreDirs)) {
			trigger_error(self::class . ': $ignoreDirs must be an array.', E_USER_WARNING);
			$ignoreDirs = preg_split('#[,\s]+#', $ignoreDirs);
		}

		$disallow = [];
		foreach (array_merge($ignoreDirs, $this->excludeDirs) as $item) {
			if ($item = realpath($item)) {
				$disallow[str_replace('\\', '/', $item)] = true;
			}
		}

		if (is_string($acceptFiles = $this->acceptFiles)) {
			trigger_error(self::class . ': $acceptFiles must be an array.', E_USER_WARNING);
			$acceptFiles = preg_split('#[,\s]+#', $acceptFiles);
		}

		$iterator = Nette\Utils\Finder::findFiles(...$acceptFiles)
			->filter(function (SplFileInfo $file) use (&$disallow) {
				return $file->getRealPath() === false
					? true
					: !isset($disallow[str_replace('\\', '/', $file->getRealPath())]);
			})
			->from($dir)
			->exclude(...$ignoreDirs)
			->filter($filter = function (SplFileInfo $dir) use (&$disallow) {
				if ($dir->getRealPath() === false) {
					return true;
				}

				$path = str_replace('\\', '/', $dir->getRealPath());
				if (is_file("$path/netterobots.txt")) {
					foreach (file("$path/netterobots.txt") as $s) {
						if (preg_match('#^(?:disallow\\s*:)?\\s*(\\S+)#i', $s, $matches)) {
							$disallow[$path . rtrim('/' . ltrim($matches[1], '/'), '/')] = true;
						}
					}
				}

				return !isset($disallow[$path]);
			});

		$filter(new SplFileInfo($dir));
		return $iterator;
	}


	private function updateFile(string $file): void
	{
		foreach ($this->classes as $class => [$prevFile]) {
			if ($file === $prevFile) {
				unset($this->classes[$class]);
			}
		}

		$foundClasses = is_file($file) ? $this->scanPhp($file) : [];

		foreach ($foundClasses as $class) {
			[$prevFile, $prevMtime] = $this->classes[$class] ?? null;

			if (isset($prevFile) && @filemtime($prevFile) !== $prevMtime) { // @ file may not exists
				$this->updateFile($prevFile);
				[$prevFile] = $this->classes[$class] ?? null;
			}

			if (isset($prevFile)) {
				throw new Nette\InvalidStateException(sprintf(
					'Ambiguous class %s resolution; defined in %s and in %s.',
					$class,
					$prevFile,
					$file
				));
			}

			$this->classes[$class] = [$file, filemtime($file)];
		}
	}


	/**
	 * Searches classes, interfaces and traits in PHP file.
	 * @return string[]
	 */
	private function scanPhp(string $file): array
	{
		$code = file_get_contents($file);
		$expected = false;
		$namespace = $name = '';
		$level = $minLevel = 0;
		$classes = [];

		try {
			$tokens = token_get_all($code, TOKEN_PARSE);
		} catch (\ParseError $e) {
			if ($this->reportParseErrors) {
				$rp = new \ReflectionProperty($e, 'file');
				$rp->setAccessible(true);
				$rp->setValue($e, $file);
				throw $e;
			}

			$tokens = [];
		}

		foreach ($tokens as $token) {
			if (is_array($token)) {
				switch ($token[0]) {
					case T_COMMENT:
					case T_DOC_COMMENT:
					case T_WHITESPACE:
						continue 2;

					case T_STRING:
					case PHP_VERSION_ID < 80000
						? T_NS_SEPARATOR
						: T_NAME_QUALIFIED:
						if ($expected) {
							$name .= $token[1];
						}

						continue 2;

					case T_NAMESPACE:
					case T_CLASS:
					case T_INTERFACE:
					case T_TRAIT:
					case PHP_VERSION_ID < 80100
						? T_CLASS
						: T_ENUM:
						$expected = $token[0];
						$name = '';
						continue 2;
					case T_CURLY_OPEN:
					case T_DOLLAR_OPEN_CURLY_BRACES:
						$level++;
				}
			}

			if ($expected) {
				if ($expected === T_NAMESPACE) {
					$namespace = $name ? $name . '\\' : '';
					$minLevel = $token === '{' ? 1 : 0;

				} elseif ($name && $level === $minLevel) {
					$classes[] = $namespace . $name;
				}

				$expected = null;
			}

			if ($token === '{') {
				$level++;
			} elseif ($token === '}') {
				$level--;
			}
		}

		return $classes;
	}


	/********************* caching ****************d*g**/


	/**
	 * Sets auto-refresh mode.
	 */
	public function setAutoRefresh(bool $on = true): self
	{
		$this->autoRebuild = $on;
		return $this;
	}


	/**
	 * Sets path to temporary directory.
	 */
	public function setTempDirectory(string $dir): self
	{
		Nette\Utils\FileSystem::createDir($dir);
		$this->tempDirectory = $dir;
		return $this;
	}


	/**
	 * Loads class list from cache.
	 */
	private function loadCache(): void
	{
		if ($this->cacheLoaded) {
			return;
		}

		$this->cacheLoaded = true;

		$file = $this->generateCacheFileName();

		// Solving atomicity to work everywhere is really pain in the ass.
		// 1) We want to do as little as possible IO calls on production and also directory and file can be not writable (#19)
		// so on Linux we include the file directly without shared lock, therefore, the file must be created atomically by renaming.
		// 2) On Windows file cannot be renamed-to while is open (ie by include() #11), so we have to acquire a lock.
		$lock = defined('PHP_WINDOWS_VERSION_BUILD')
			? $this->acquireLock("$file.lock", LOCK_SH)
			: null;

		$data = @include $file; // @ file may not exist
		if (is_array($data)) {
			[$this->classes, $this->missingClasses, $this->emptyFiles] = $data;
			return;
		}

		if ($lock) {
			flock($lock, LOCK_UN); // release shared lock so we can get exclusive
		}

		$lock = $this->acquireLock("$file.lock", LOCK_EX);

		// while waiting for exclusive lock, someone might have already created the cache
		$data = @include $file; // @ file may not exist
		if (is_array($data)) {
			[$this->classes, $this->missingClasses, $this->emptyFiles] = $data;
			return;
		}

		$this->classes = $this->missingClasses = $this->emptyFiles = [];
		$this->refreshClasses();
		$this->saveCache($lock);
		// On Windows concurrent creation and deletion of a file can cause a 'permission denied' error,
		// therefore, we will not delete the lock file. Windows is really annoying.
	}


	/**
	 * Writes class list to cache.
	 * @param  resource  $lock
	 */
	private function saveCache($lock = null): void
	{
		// we have to acquire a lock to be able safely rename file
		// on Linux: that another thread does not rename the same named file earlier
		// on Windows: that the file is not read by another thread
		$file = $this->generateCacheFileName();
		$lock = $lock ?: $this->acquireLock("$file.lock", LOCK_EX);
		$code = "<?php\nreturn " . var_export([$this->classes, $this->missingClasses, $this->emptyFiles], true) . ";\n";

		if (file_put_contents("$file.tmp", $code) !== strlen($code) || !rename("$file.tmp", $file)) {
			@unlink("$file.tmp"); // @ file may not exist
			throw new \RuntimeException(sprintf("Unable to create '%s'.", $file));
		}

		if (function_exists('opcache_invalidate')) {
			@opcache_invalidate($file, true); // @ can be restricted
		}
	}


	/** @return resource */
	private function acquireLock(string $file, int $mode)
	{
		$handle = @fopen($file, 'w'); // @ is escalated to exception
		if (!$handle) {
			throw new \RuntimeException(sprintf("Unable to create file '%s'. %s", $file, error_get_last()['message']));
		} elseif (!@flock($handle, $mode)) { // @ is escalated to exception
			throw new \RuntimeException(sprintf(
				"Unable to acquire %s lock on file '%s'. %s",
				$mode & LOCK_EX ? 'exclusive' : 'shared',
				$file,
				error_get_last()['message']
			));
		}

		return $handle;
	}


	private function generateCacheFileName(): string
	{
		if (!$this->tempDirectory) {
			throw new \LogicException('Set path to temporary directory using setTempDirectory().');
		}

		return $this->tempDirectory . '/' . md5(serialize($this->getCacheKey())) . '.php';
	}


	protected function getCacheKey(): array
	{
		return [$this->ignoreDirs, $this->acceptFiles, $this->scanPaths, $this->excludeDirs, 'v2'];
	}
}
