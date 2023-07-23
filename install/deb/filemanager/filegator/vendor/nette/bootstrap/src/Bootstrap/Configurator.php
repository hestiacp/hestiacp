<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bootstrap;

use Composer\Autoload\ClassLoader;
use Latte;
use Nette;
use Nette\DI;
use Tracy;


/**
 * Initial system DI container generator.
 */
class Configurator
{
	use Nette\SmartObject;

	public const CookieSecret = 'nette-debug';

	/** @deprecated  use Configurator::CookieSecret */
	public const COOKIE_SECRET = self::CookieSecret;


	/** @var callable[]  function (Configurator $sender, DI\Compiler $compiler); Occurs after the compiler is created */
	public $onCompile = [];

	/** @var array */
	public $defaultExtensions = [
		'application' => [Nette\Bridges\ApplicationDI\ApplicationExtension::class, ['%debugMode%', ['%appDir%'], '%tempDir%/cache/nette.application']],
		'cache' => [Nette\Bridges\CacheDI\CacheExtension::class, ['%tempDir%']],
		'constants' => Extensions\ConstantsExtension::class,
		'database' => [Nette\Bridges\DatabaseDI\DatabaseExtension::class, ['%debugMode%']],
		'decorator' => Nette\DI\Extensions\DecoratorExtension::class,
		'di' => [Nette\DI\Extensions\DIExtension::class, ['%debugMode%']],
		'extensions' => Nette\DI\Extensions\ExtensionsExtension::class,
		'forms' => Nette\Bridges\FormsDI\FormsExtension::class,
		'http' => [Nette\Bridges\HttpDI\HttpExtension::class, ['%consoleMode%']],
		'inject' => Nette\DI\Extensions\InjectExtension::class,
		'latte' => [Nette\Bridges\ApplicationDI\LatteExtension::class, ['%tempDir%/cache/latte', '%debugMode%']],
		'mail' => Nette\Bridges\MailDI\MailExtension::class,
		'php' => Extensions\PhpExtension::class,
		'routing' => [Nette\Bridges\ApplicationDI\RoutingExtension::class, ['%debugMode%']],
		'search' => [Nette\DI\Extensions\SearchExtension::class, ['%tempDir%/cache/nette.search']],
		'security' => [Nette\Bridges\SecurityDI\SecurityExtension::class, ['%debugMode%']],
		'session' => [Nette\Bridges\HttpDI\SessionExtension::class, ['%debugMode%', '%consoleMode%']],
		'tracy' => [Tracy\Bridges\Nette\TracyExtension::class, ['%debugMode%', '%consoleMode%']],
	];

	/** @var string[] of classes which shouldn't be autowired */
	public $autowireExcludedClasses = [
		\ArrayAccess::class,
		\Countable::class,
		\IteratorAggregate::class,
		\stdClass::class,
		\Traversable::class,
	];

	/** @var array */
	protected $staticParameters;

	/** @var array */
	protected $dynamicParameters = [];

	/** @var array */
	protected $services = [];

	/** @var array of string|array */
	protected $configs = [];


	public function __construct()
	{
		$this->staticParameters = $this->getDefaultParameters();
	}


	/**
	 * Set parameter %debugMode%.
	 * @param  bool|string|array  $value
	 * @return static
	 */
	public function setDebugMode($value)
	{
		if (is_string($value) || is_array($value)) {
			$value = static::detectDebugMode($value);
		} elseif (!is_bool($value)) {
			throw new Nette\InvalidArgumentException(sprintf('Value must be either a string, array, or boolean, %s given.', gettype($value)));
		}

		$this->staticParameters['debugMode'] = $value;
		$this->staticParameters['productionMode'] = !$this->staticParameters['debugMode']; // compatibility
		return $this;
	}


	public function isDebugMode(): bool
	{
		return $this->staticParameters['debugMode'];
	}


	/**
	 * Sets path to temporary directory.
	 * @return static
	 */
	public function setTempDirectory(string $path)
	{
		$this->staticParameters['tempDir'] = $path;
		return $this;
	}


	/**
	 * Sets the default timezone.
	 * @return static
	 */
	public function setTimeZone(string $timezone)
	{
		date_default_timezone_set($timezone);
		@ini_set('date.timezone', $timezone); // @ - function may be disabled
		return $this;
	}


	/**
	 * Alias for addStaticParameters()
	 * @return static
	 */
	public function addParameters(array $params)
	{
		return $this->addStaticParameters($params);
	}


	/**
	 * Adds new static parameters.
	 * @return static
	 */
	public function addStaticParameters(array $params)
	{
		$this->staticParameters = DI\Config\Helpers::merge($params, $this->staticParameters);
		return $this;
	}


	/**
	 * Adds new dynamic parameters.
	 * @return static
	 */
	public function addDynamicParameters(array $params)
	{
		$this->dynamicParameters = $params + $this->dynamicParameters;
		return $this;
	}


	/**
	 * Add instances of services.
	 * @return static
	 */
	public function addServices(array $services)
	{
		$this->services = $services + $this->services;
		return $this;
	}


	protected function getDefaultParameters(): array
	{
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$last = end($trace);
		$debugMode = static::detectDebugMode();
		$loaderRc = class_exists(ClassLoader::class)
			? new \ReflectionClass(ClassLoader::class)
			: null;
		return [
			'appDir' => isset($trace[1]['file']) ? dirname($trace[1]['file']) : null,
			'wwwDir' => isset($last['file']) ? dirname($last['file']) : null,
			'vendorDir' => $loaderRc ? dirname($loaderRc->getFileName(), 2) : null,
			'debugMode' => $debugMode,
			'productionMode' => !$debugMode,
			'consoleMode' => PHP_SAPI === 'cli',
		];
	}


	public function enableTracy(?string $logDirectory = null, ?string $email = null): void
	{
		if (!class_exists(Tracy\Debugger::class)) {
			throw new Nette\NotSupportedException('Tracy not found, do you have `tracy/tracy` package installed?');
		}

		Tracy\Debugger::$strictMode = true;
		Tracy\Debugger::enable(!$this->staticParameters['debugMode'], $logDirectory, $email);
		Tracy\Bridges\Nette\Bridge::initialize();
		if (class_exists(Latte\Bridges\Tracy\BlueScreenPanel::class)) {
			Latte\Bridges\Tracy\BlueScreenPanel::initialize();
		}
	}


	/**
	 * Alias for enableTracy()
	 */
	public function enableDebugger(?string $logDirectory = null, ?string $email = null): void
	{
		$this->enableTracy($logDirectory, $email);
	}


	/**
	 * @throws Nette\NotSupportedException if RobotLoader is not available
	 */
	public function createRobotLoader(): Nette\Loaders\RobotLoader
	{
		if (!class_exists(Nette\Loaders\RobotLoader::class)) {
			throw new Nette\NotSupportedException('RobotLoader not found, do you have `nette/robot-loader` package installed?');
		}

		$loader = new Nette\Loaders\RobotLoader;
		$loader->setTempDirectory($this->getCacheDirectory() . '/nette.robotLoader');
		$loader->setAutoRefresh($this->staticParameters['debugMode']);

		if (isset($this->defaultExtensions['application'])) {
			$this->defaultExtensions['application'][1][1] = null;
			$this->defaultExtensions['application'][1][3] = $loader;
		}

		return $loader;
	}


	/**
	 * Adds configuration file.
	 * @param  string|array  $config
	 * @return static
	 */
	public function addConfig($config)
	{
		$this->configs[] = $config;
		return $this;
	}


	/**
	 * Returns system DI container.
	 */
	public function createContainer(bool $initialize = true): DI\Container
	{
		$class = $this->loadContainer();
		$container = new $class($this->dynamicParameters);
		foreach ($this->services as $name => $service) {
			$container->addService($name, $service);
		}

		if ($initialize) {
			$container->initialize();
		}

		return $container;
	}


	/**
	 * Loads system DI container class and returns its name.
	 */
	public function loadContainer(): string
	{
		$loader = new DI\ContainerLoader(
			$this->getCacheDirectory() . '/nette.configurator',
			$this->staticParameters['debugMode']
		);
		return $loader->load(
			[$this, 'generateContainer'],
			$this->generateContainerKey()
		);
	}


	/**
	 * @internal
	 */
	public function generateContainer(DI\Compiler $compiler): void
	{
		$loader = $this->createLoader();
		$loader->setParameters($this->staticParameters);

		foreach ($this->configs as $config) {
			if (is_string($config)) {
				$compiler->loadConfig($config, $loader);
			} else {
				$compiler->addConfig($config);
			}
		}

		$compiler->addConfig(['parameters' => DI\Helpers::escape($this->staticParameters)]);
		$compiler->setDynamicParameterNames(array_keys($this->dynamicParameters));

		$builder = $compiler->getContainerBuilder();
		$builder->addExcludedClasses($this->autowireExcludedClasses);

		foreach ($this->defaultExtensions as $name => $extension) {
			[$class, $args] = is_string($extension)
				? [$extension, []]
				: $extension;
			if (class_exists($class)) {
				$args = DI\Helpers::expand($args, $this->staticParameters);
				$compiler->addExtension($name, (new \ReflectionClass($class))->newInstanceArgs($args));
			}
		}

		Nette\Utils\Arrays::invoke($this->onCompile, $this, $compiler);
	}


	protected function createLoader(): DI\Config\Loader
	{
		return new DI\Config\Loader;
	}


	protected function generateContainerKey(): array
	{
		return [
			$this->staticParameters,
			array_keys($this->dynamicParameters),
			$this->configs,
			PHP_VERSION_ID - PHP_RELEASE_VERSION, // minor PHP version
			class_exists(ClassLoader::class) // composer update
				? filemtime((new \ReflectionClass(ClassLoader::class))->getFilename())
				: null,
		];
	}


	protected function getCacheDirectory(): string
	{
		if (empty($this->staticParameters['tempDir'])) {
			throw new Nette\InvalidStateException('Set path to temporary directory using setTempDirectory().');
		}

		$dir = $this->staticParameters['tempDir'] . '/cache';
		Nette\Utils\FileSystem::createDir($dir);
		return $dir;
	}


	/********************* tools ****************d*g**/


	/**
	 * Detects debug mode by IP addresses or computer names whitelist detection.
	 * @param  string|array  $list
	 */
	public static function detectDebugMode($list = null): bool
	{
		$addr = $_SERVER['REMOTE_ADDR'] ?? php_uname('n');
		$secret = is_string($_COOKIE[self::CookieSecret] ?? null)
			? $_COOKIE[self::CookieSecret]
			: null;
		$list = is_string($list)
			? preg_split('#[,\s]+#', $list)
			: (array) $list;
		if (!isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !isset($_SERVER['HTTP_FORWARDED'])) {
			$list[] = '127.0.0.1';
			$list[] = '::1';
			$list[] = '[::1]'; // workaround for PHP < 7.3.4
		}

		return in_array($addr, $list, true) || in_array("$secret@$addr", $list, true);
	}
}


class_exists(Nette\Configurator::class);
