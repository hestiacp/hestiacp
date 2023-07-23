<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;
use Nette\InvalidStateException;


/**
 * Namespaced part of a PHP file.
 *
 * Generates:
 * - namespace statement
 * - variable amount of use statements
 * - one or more class declarations
 */
final class PhpNamespace
{
	use Nette\SmartObject;

	public const
		NameNormal = 'n',
		NameFunction = 'f',
		NameConstant = 'c';

	public const
		NAME_NORMAL = self::NameNormal,
		NAME_FUNCTION = self::NameFunction,
		NAME_CONSTANT = self::NameConstant;

	/** @var string */
	private $name;

	/** @var bool */
	private $bracketedSyntax = false;

	/** @var string[][] */
	private $aliases = [
		self::NameNormal => [],
		self::NameFunction => [],
		self::NameConstant => [],
	];

	/** @var ClassType[] */
	private $classes = [];

	/** @var GlobalFunction[] */
	private $functions = [];


	public function __construct(string $name)
	{
		if ($name !== '' && !Helpers::isNamespaceIdentifier($name)) {
			throw new Nette\InvalidArgumentException("Value '$name' is not valid name.");
		}

		$this->name = $name;
	}


	public function getName(): string
	{
		return $this->name;
	}


	/**
	 * @return static
	 * @internal
	 */
	public function setBracketedSyntax(bool $state = true): self
	{
		$this->bracketedSyntax = $state;
		return $this;
	}


	public function hasBracketedSyntax(): bool
	{
		return $this->bracketedSyntax;
	}


	/** @deprecated  use hasBracketedSyntax() */
	public function getBracketedSyntax(): bool
	{
		return $this->bracketedSyntax;
	}


	/**
	 * @throws InvalidStateException
	 * @return static
	 */
	public function addUse(string $name, ?string $alias = null, string $of = self::NameNormal): self
	{
		if (
			!Helpers::isNamespaceIdentifier($name, true)
			|| (Helpers::isIdentifier($name) && isset(Helpers::Keywords[strtolower($name)]))
		) {
			throw new Nette\InvalidArgumentException("Value '$name' is not valid class/function/constant name.");

		} elseif ($alias && (!Helpers::isIdentifier($alias) || isset(Helpers::Keywords[strtolower($alias)]))) {
			throw new Nette\InvalidArgumentException("Value '$alias' is not valid alias.");
		}

		$name = ltrim($name, '\\');
		$aliases = array_change_key_case($this->aliases[$of]);
		$used = [self::NameNormal => $this->classes, self::NameFunction => $this->functions, self::NameConstant => []][$of];

		if ($alias === null) {
			$base = Helpers::extractShortName($name);
			$counter = null;
			do {
				$alias = $base . $counter;
				$lower = strtolower($alias);
				$counter++;
			} while ((isset($aliases[$lower]) && strcasecmp($aliases[$lower], $name) !== 0) || isset($used[$lower]));
		} else {
			$lower = strtolower($alias);
			if (isset($aliases[$lower]) && strcasecmp($aliases[$lower], $name) !== 0) {
				throw new InvalidStateException(
					"Alias '$alias' used already for '{$aliases[$lower]}', cannot use for '$name'."
				);
			} elseif (isset($used[$lower])) {
				throw new Nette\InvalidStateException("Name '$alias' used already for '$this->name\\{$used[$lower]->getName()}'.");
			}
		}

		$this->aliases[$of][$alias] = $name;
		return $this;
	}


	public function removeUse(string $name, string $of = self::NameNormal): void
	{
		foreach ($this->aliases[$of] as $alias => $item) {
			if (strcasecmp($item, $name) === 0) {
				unset($this->aliases[$of][$alias]);
			}
		}
	}


	/** @return static */
	public function addUseFunction(string $name, ?string $alias = null): self
	{
		return $this->addUse($name, $alias, self::NameFunction);
	}


	/** @return static */
	public function addUseConstant(string $name, ?string $alias = null): self
	{
		return $this->addUse($name, $alias, self::NameConstant);
	}


	/** @return string[] */
	public function getUses(string $of = self::NameNormal): array
	{
		asort($this->aliases[$of]);
		return array_filter(
			$this->aliases[$of],
			function ($name, $alias) { return strcasecmp(($this->name ? $this->name . '\\' : '') . $alias, $name); },
			ARRAY_FILTER_USE_BOTH
		);
	}


	/** @deprecated  use simplifyName() */
	public function unresolveName(string $name): string
	{
		return $this->simplifyName($name);
	}


	public function resolveName(string $name, string $of = self::NameNormal): string
	{
		if (isset(Helpers::Keywords[strtolower($name)]) || $name === '') {
			return $name;
		} elseif ($name[0] === '\\') {
			return substr($name, 1);
		}

		$aliases = array_change_key_case($this->aliases[$of]);
		if ($of !== self::NameNormal) {
			return $aliases[strtolower($name)]
				?? $this->resolveName(Helpers::extractNamespace($name) . '\\') . Helpers::extractShortName($name);
		}

		$parts = explode('\\', $name, 2);
		return ($res = $aliases[strtolower($parts[0])] ?? null)
			? $res . (isset($parts[1]) ? '\\' . $parts[1] : '')
			: $this->name . ($this->name ? '\\' : '') . $name;
	}


	public function simplifyType(string $type, string $of = self::NameNormal): string
	{
		return preg_replace_callback('~[\w\x7f-\xff\\\\]+~', function ($m) use ($of) { return $this->simplifyName($m[0], $of); }, $type);
	}


	public function simplifyName(string $name, string $of = self::NameNormal): string
	{
		if (isset(Helpers::Keywords[strtolower($name)]) || $name === '') {
			return $name;
		}

		$name = ltrim($name, '\\');

		if ($of !== self::NameNormal) {
			foreach ($this->aliases[$of] as $alias => $original) {
				if (strcasecmp($original, $name) === 0) {
					return $alias;
				}
			}

			return $this->simplifyName(Helpers::extractNamespace($name) . '\\') . Helpers::extractShortName($name);
		}

		$shortest = null;
		$relative = self::startsWith($name, $this->name . '\\')
			? substr($name, strlen($this->name) + 1)
			: null;

		foreach ($this->aliases[$of] as $alias => $original) {
			if ($relative && self::startsWith($relative . '\\', $alias . '\\')) {
				$relative = null;
			}

			if (self::startsWith($name . '\\', $original . '\\')) {
				$short = $alias . substr($name, strlen($original));
				if (!isset($shortest) || strlen($shortest) > strlen($short)) {
					$shortest = $short;
				}
			}
		}

		if (isset($shortest, $relative) && strlen($shortest) < strlen($relative)) {
			return $shortest;
		}

		return $relative ?? $shortest ?? ($this->name ? '\\' : '') . $name;
	}


	/** @return static */
	public function add(ClassType $class): self
	{
		$name = $class->getName();
		if ($name === null) {
			throw new Nette\InvalidArgumentException('Class does not have a name.');
		}

		$lower = strtolower($name);
		if ($orig = array_change_key_case($this->aliases[self::NameNormal])[$lower] ?? null) {
			throw new Nette\InvalidStateException("Name '$name' used already as alias for $orig.");
		}

		$this->classes[$lower] = $class;
		return $this;
	}


	public function addClass(string $name): ClassType
	{
		$this->add($class = new ClassType($name, $this));
		return $class;
	}


	public function addInterface(string $name): ClassType
	{
		return $this->addClass($name)->setType(ClassType::TYPE_INTERFACE);
	}


	public function addTrait(string $name): ClassType
	{
		return $this->addClass($name)->setType(ClassType::TYPE_TRAIT);
	}


	public function addEnum(string $name): ClassType
	{
		return $this->addClass($name)->setType(ClassType::TYPE_ENUM);
	}


	public function removeClass(string $name): self
	{
		unset($this->classes[strtolower($name)]);
		return $this;
	}


	public function addFunction(string $name): GlobalFunction
	{
		$lower = strtolower($name);
		if ($orig = array_change_key_case($this->aliases[self::NameFunction])[$lower] ?? null) {
			throw new Nette\InvalidStateException("Name '$name' used already as alias for $orig.");
		}

		return $this->functions[$lower] = new GlobalFunction($name);
	}


	public function removeFunction(string $name): self
	{
		unset($this->functions[strtolower($name)]);
		return $this;
	}


	/** @return ClassType[] */
	public function getClasses(): array
	{
		$res = [];
		foreach ($this->classes as $class) {
			$res[$class->getName()] = $class;
		}

		return $res;
	}


	/** @return GlobalFunction[] */
	public function getFunctions(): array
	{
		$res = [];
		foreach ($this->functions as $fn) {
			$res[$fn->getName()] = $fn;
		}

		return $res;
	}


	private static function startsWith(string $a, string $b): bool
	{
		return strncasecmp($a, $b, strlen($b)) === 0;
	}


	public function __toString(): string
	{
		try {
			return (new Printer)->printNamespace($this);
		} catch (\Throwable $e) {
			if (PHP_VERSION_ID >= 70400) {
				throw $e;
			}

			trigger_error('Exception in ' . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", E_USER_ERROR);
			return '';
		}
	}
}
