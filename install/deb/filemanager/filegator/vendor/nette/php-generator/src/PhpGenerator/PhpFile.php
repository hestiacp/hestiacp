<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


/**
 * Instance of PHP file.
 *
 * Generates:
 * - opening tag (<?php)
 * - doc comments
 * - one or more namespaces
 */
final class PhpFile
{
	use Nette\SmartObject;
	use Traits\CommentAware;

	/** @var PhpNamespace[] */
	private $namespaces = [];

	/** @var bool */
	private $strictTypes = false;


	public static function fromCode(string $code): self
	{
		return (new Factory)->fromCode($code);
	}


	public function addClass(string $name): ClassType
	{
		return $this
			->addNamespace(Helpers::extractNamespace($name))
			->addClass(Helpers::extractShortName($name));
	}


	public function addInterface(string $name): ClassType
	{
		return $this
			->addNamespace(Helpers::extractNamespace($name))
			->addInterface(Helpers::extractShortName($name));
	}


	public function addTrait(string $name): ClassType
	{
		return $this
			->addNamespace(Helpers::extractNamespace($name))
			->addTrait(Helpers::extractShortName($name));
	}


	public function addEnum(string $name): ClassType
	{
		return $this
			->addNamespace(Helpers::extractNamespace($name))
			->addEnum(Helpers::extractShortName($name));
	}


	/** @param  string|PhpNamespace  $namespace */
	public function addNamespace($namespace): PhpNamespace
	{
		if ($namespace instanceof PhpNamespace) {
			$res = $this->namespaces[$namespace->getName()] = $namespace;

		} elseif (is_string($namespace)) {
			$res = $this->namespaces[$namespace] = $this->namespaces[$namespace] ?? new PhpNamespace($namespace);

		} else {
			throw new Nette\InvalidArgumentException('Argument must be string|PhpNamespace.');
		}

		foreach ($this->namespaces as $namespace) {
			$namespace->setBracketedSyntax(count($this->namespaces) > 1 && isset($this->namespaces['']));
		}

		return $res;
	}


	public function addFunction(string $name): GlobalFunction
	{
		return $this
			->addNamespace(Helpers::extractNamespace($name))
			->addFunction(Helpers::extractShortName($name));
	}


	/** @return PhpNamespace[] */
	public function getNamespaces(): array
	{
		return $this->namespaces;
	}


	/** @return ClassType[] */
	public function getClasses(): array
	{
		$classes = [];
		foreach ($this->namespaces as $n => $namespace) {
			$n .= $n ? '\\' : '';
			foreach ($namespace->getClasses() as $c => $class) {
				$classes[$n . $c] = $class;
			}
		}

		return $classes;
	}


	/** @return GlobalFunction[] */
	public function getFunctions(): array
	{
		$functions = [];
		foreach ($this->namespaces as $n => $namespace) {
			$n .= $n ? '\\' : '';
			foreach ($namespace->getFunctions() as $f => $function) {
				$functions[$n . $f] = $function;
			}
		}

		return $functions;
	}


	/** @return static */
	public function addUse(string $name, ?string $alias = null, string $of = PhpNamespace::NameNormal): self
	{
		$this->addNamespace('')->addUse($name, $alias, $of);
		return $this;
	}


	/**
	 * Adds declare(strict_types=1) to output.
	 * @return static
	 */
	public function setStrictTypes(bool $on = true): self
	{
		$this->strictTypes = $on;
		return $this;
	}


	public function hasStrictTypes(): bool
	{
		return $this->strictTypes;
	}


	/** @deprecated  use hasStrictTypes() */
	public function getStrictTypes(): bool
	{
		return $this->strictTypes;
	}


	public function __toString(): string
	{
		try {
			return (new Printer)->printFile($this);
		} catch (\Throwable $e) {
			if (PHP_VERSION_ID >= 70400) {
				throw $e;
			}

			trigger_error('Exception in ' . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", E_USER_ERROR);
			return '';
		}
	}
}
