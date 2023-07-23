<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


/**
 * Class/Interface/Trait/Enum description.
 *
 * @property Method[] $methods
 * @property Property[] $properties
 */
final class ClassType
{
	use Nette\SmartObject;
	use Traits\CommentAware;
	use Traits\AttributeAware;

	public const
		TYPE_CLASS = 'class',
		TYPE_INTERFACE = 'interface',
		TYPE_TRAIT = 'trait',
		TYPE_ENUM = 'enum';

	public const
		VisibilityPublic = 'public',
		VisibilityProtected = 'protected',
		VisibilityPrivate = 'private';

	public const
		VISIBILITY_PUBLIC = self::VisibilityPublic,
		VISIBILITY_PROTECTED = self::VisibilityProtected,
		VISIBILITY_PRIVATE = self::VisibilityPrivate;

	/** @var PhpNamespace|null */
	private $namespace;

	/** @var string|null */
	private $name;

	/** @var string  class|interface|trait */
	private $type = self::TYPE_CLASS;

	/** @var bool */
	private $final = false;

	/** @var bool */
	private $abstract = false;

	/** @var string|string[] */
	private $extends = [];

	/** @var string[] */
	private $implements = [];

	/** @var TraitUse[] */
	private $traits = [];

	/** @var Constant[] name => Constant */
	private $consts = [];

	/** @var Property[] name => Property */
	private $properties = [];

	/** @var Method[] name => Method */
	private $methods = [];

	/** @var EnumCase[] name => EnumCase */
	private $cases = [];


	public static function class(?string $name): self
	{
		return new self($name);
	}


	public static function interface(string $name): self
	{
		return (new self($name))->setType(self::TYPE_INTERFACE);
	}


	public static function trait(string $name): self
	{
		return (new self($name))->setType(self::TYPE_TRAIT);
	}


	public static function enum(string $name): self
	{
		return (new self($name))->setType(self::TYPE_ENUM);
	}


	/**
	 * @param  string|object  $class
	 */
	public static function from($class, bool $withBodies = false, bool $materializeTraits = true): self
	{
		return (new Factory)
			->fromClassReflection(new \ReflectionClass($class), $withBodies, $materializeTraits);
	}


	/**
	 * @param  string|object  $class
	 */
	public static function withBodiesFrom($class): self
	{
		return (new Factory)
			->fromClassReflection(new \ReflectionClass($class), true);
	}


	public static function fromCode(string $code): self
	{
		return (new Factory)
			->fromClassCode($code);
	}


	public function __construct(?string $name = null, ?PhpNamespace $namespace = null)
	{
		$this->setName($name);
		$this->namespace = $namespace;
	}


	public function __toString(): string
	{
		try {
			return (new Printer)->printClass($this, $this->namespace);
		} catch (\Throwable $e) {
			if (PHP_VERSION_ID >= 70400) {
				throw $e;
			}

			trigger_error('Exception in ' . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", E_USER_ERROR);
			return '';
		}
	}


	/** @deprecated  an object can be in multiple namespaces */
	public function getNamespace(): ?PhpNamespace
	{
		return $this->namespace;
	}


	/** @return static */
	public function setName(?string $name): self
	{
		if ($name !== null && (!Helpers::isIdentifier($name) || isset(Helpers::Keywords[strtolower($name)]))) {
			throw new Nette\InvalidArgumentException("Value '$name' is not valid class name.");
		}

		$this->name = $name;
		return $this;
	}


	public function getName(): ?string
	{
		return $this->name;
	}


	/** @deprecated */
	public function setClass(): self
	{
		$this->type = self::TYPE_CLASS;
		return $this;
	}


	public function isClass(): bool
	{
		return $this->type === self::TYPE_CLASS;
	}


	/** @return static */
	public function setInterface(): self
	{
		$this->type = self::TYPE_INTERFACE;
		return $this;
	}


	public function isInterface(): bool
	{
		return $this->type === self::TYPE_INTERFACE;
	}


	/** @return static */
	public function setTrait(): self
	{
		$this->type = self::TYPE_TRAIT;
		return $this;
	}


	public function isTrait(): bool
	{
		return $this->type === self::TYPE_TRAIT;
	}


	public function isEnum(): bool
	{
		return $this->type === self::TYPE_ENUM;
	}


	/** @return static */
	public function setType(string $type): self
	{
		if (!in_array($type, [self::TYPE_CLASS, self::TYPE_INTERFACE, self::TYPE_TRAIT, self::TYPE_ENUM], true)) {
			throw new Nette\InvalidArgumentException('Argument must be class|interface|trait|enum.');
		}

		$this->type = $type;
		return $this;
	}


	public function getType(): string
	{
		return $this->type;
	}


	/** @return static */
	public function setFinal(bool $state = true): self
	{
		$this->final = $state;
		return $this;
	}


	public function isFinal(): bool
	{
		return $this->final;
	}


	/** @return static */
	public function setAbstract(bool $state = true): self
	{
		$this->abstract = $state;
		return $this;
	}


	public function isAbstract(): bool
	{
		return $this->abstract;
	}


	/**
	 * @param  string|string[]  $names
	 * @return static
	 */
	public function setExtends($names): self
	{
		if (!is_string($names) && !is_array($names)) {
			throw new Nette\InvalidArgumentException('Argument must be string or string[].');
		}

		$this->validateNames((array) $names);
		$this->extends = $names;
		return $this;
	}


	/** @return string|string[] */
	public function getExtends()
	{
		return $this->extends;
	}


	/** @return static */
	public function addExtend(string $name): self
	{
		$this->validateNames([$name]);
		$this->extends = (array) $this->extends;
		$this->extends[] = $name;
		return $this;
	}


	/**
	 * @param  string[]  $names
	 * @return static
	 */
	public function setImplements(array $names): self
	{
		$this->validateNames($names);
		$this->implements = $names;
		return $this;
	}


	/** @return string[] */
	public function getImplements(): array
	{
		return $this->implements;
	}


	/** @return static */
	public function addImplement(string $name): self
	{
		$this->validateNames([$name]);
		$this->implements[] = $name;
		return $this;
	}


	/** @return static */
	public function removeImplement(string $name): self
	{
		$this->implements = array_diff($this->implements, [$name]);
		return $this;
	}


	/**
	 * @param  string[]|TraitUse[]  $traits
	 * @return static
	 */
	public function setTraits(array $traits): self
	{
		$this->traits = [];
		foreach ($traits as $trait) {
			if (!$trait instanceof TraitUse) {
				$trait = new TraitUse($trait);
			}

			$this->traits[$trait->getName()] = $trait;
		}

		return $this;
	}


	/** @return string[] */
	public function getTraits(): array
	{
		return array_keys($this->traits);
	}


	/** @internal */
	public function getTraitResolutions(): array
	{
		return $this->traits;
	}


	/**
	 * @param  array|bool  $resolutions
	 * @return static|TraitUse
	 */
	public function addTrait(string $name, $resolutions = [])
	{
		$this->traits[$name] = $trait = new TraitUse($name);
		if ($resolutions === true) {
			return $trait;
		}

		array_map(function ($item) use ($trait) {
			$trait->addResolution($item);
		}, $resolutions);
		return $this;
	}


	/** @return static */
	public function removeTrait(string $name): self
	{
		unset($this->traits[$name]);
		return $this;
	}


	/**
	 * @param  Method|Property|Constant|EnumCase|TraitUse  $member
	 * @return static
	 */
	public function addMember($member): self
	{
		if ($member instanceof Method) {
			if ($this->isInterface()) {
				$member->setBody(null);
			}

			$this->methods[strtolower($member->getName())] = $member;

		} elseif ($member instanceof Property) {
			$this->properties[$member->getName()] = $member;

		} elseif ($member instanceof Constant) {
			$this->consts[$member->getName()] = $member;

		} elseif ($member instanceof EnumCase) {
			$this->cases[$member->getName()] = $member;

		} elseif ($member instanceof TraitUse) {
			$this->traits[$member->getName()] = $member;

		} else {
			throw new Nette\InvalidArgumentException('Argument must be Method|Property|Constant|EnumCase|TraitUse.');
		}

		return $this;
	}


	/**
	 * @param  Constant[]|mixed[]  $consts
	 * @return static
	 */
	public function setConstants(array $consts): self
	{
		$this->consts = [];
		foreach ($consts as $k => $const) {
			if (!$const instanceof Constant) {
				$const = (new Constant($k))->setValue($const)->setPublic();
			}

			$this->consts[$const->getName()] = $const;
		}

		return $this;
	}


	/** @return Constant[] */
	public function getConstants(): array
	{
		return $this->consts;
	}


	public function addConstant(string $name, $value): Constant
	{
		return $this->consts[$name] = (new Constant($name))
			->setValue($value)
			->setPublic();
	}


	/** @return static */
	public function removeConstant(string $name): self
	{
		unset($this->consts[$name]);
		return $this;
	}


	/**
	 * Sets cases to enum
	 * @param  EnumCase[]  $cases
	 * @return static
	 */
	public function setCases(array $cases): self
	{
		(function (EnumCase ...$cases) {})(...array_values($cases));
		$this->cases = [];
		foreach ($cases as $case) {
			$this->cases[$case->getName()] = $case;
		}

		return $this;
	}


	/** @return EnumCase[] */
	public function getCases(): array
	{
		return $this->cases;
	}


	/** Adds case to enum */
	public function addCase(string $name, $value = null): EnumCase
	{
		return $this->cases[$name] = (new EnumCase($name))
			->setValue($value);
	}


	/** @return static */
	public function removeCase(string $name): self
	{
		unset($this->cases[$name]);
		return $this;
	}


	/**
	 * @param  Property[]  $props
	 * @return static
	 */
	public function setProperties(array $props): self
	{
		(function (Property ...$props) {})(...array_values($props));
		$this->properties = [];
		foreach ($props as $v) {
			$this->properties[$v->getName()] = $v;
		}

		return $this;
	}


	/** @return Property[] */
	public function getProperties(): array
	{
		return $this->properties;
	}


	public function getProperty(string $name): Property
	{
		if (!isset($this->properties[$name])) {
			throw new Nette\InvalidArgumentException("Property '$name' not found.");
		}

		return $this->properties[$name];
	}


	/**
	 * @param  string  $name  without $
	 */
	public function addProperty(string $name, $value = null): Property
	{
		return $this->properties[$name] = func_num_args() > 1
			? (new Property($name))->setValue($value)
			: new Property($name);
	}


	/**
	 * @param  string  $name without $
	 * @return static
	 */
	public function removeProperty(string $name): self
	{
		unset($this->properties[$name]);
		return $this;
	}


	public function hasProperty(string $name): bool
	{
		return isset($this->properties[$name]);
	}


	/**
	 * @param  Method[]  $methods
	 * @return static
	 */
	public function setMethods(array $methods): self
	{
		(function (Method ...$methods) {})(...array_values($methods));
		$this->methods = [];
		foreach ($methods as $m) {
			$this->methods[strtolower($m->getName())] = $m;
		}

		return $this;
	}


	/** @return Method[] */
	public function getMethods(): array
	{
		$res = [];
		foreach ($this->methods as $m) {
			$res[$m->getName()] = $m;
		}

		return $res;
	}


	public function getMethod(string $name): Method
	{
		$m = $this->methods[strtolower($name)] ?? null;
		if (!$m) {
			throw new Nette\InvalidArgumentException("Method '$name' not found.");
		}

		return $m;
	}


	public function addMethod(string $name): Method
	{
		$method = new Method($name);
		if ($this->isInterface()) {
			$method->setBody(null);
		} else {
			$method->setPublic();
		}

		return $this->methods[strtolower($name)] = $method;
	}


	/** @return static */
	public function removeMethod(string $name): self
	{
		unset($this->methods[strtolower($name)]);
		return $this;
	}


	public function hasMethod(string $name): bool
	{
		return isset($this->methods[strtolower($name)]);
	}


	/** @throws Nette\InvalidStateException */
	public function validate(): void
	{
		if ($this->isEnum() && ($this->abstract || $this->final || $this->extends || $this->properties)) {
			throw new Nette\InvalidStateException("Enum '$this->name' cannot be abstract or final or extends class or have properties.");

		} elseif (!$this->name && ($this->abstract || $this->final)) {
			throw new Nette\InvalidStateException('Anonymous class cannot be abstract or final.');

		} elseif ($this->abstract && $this->final) {
			throw new Nette\InvalidStateException("Class '$this->name' cannot be abstract and final at the same time.");
		}
	}


	private function validateNames(array $names): void
	{
		foreach ($names as $name) {
			if (!Helpers::isNamespaceIdentifier($name, true)) {
				throw new Nette\InvalidArgumentException("Value '$name' is not valid class name.");
			}
		}
	}


	public function __clone()
	{
		$clone = function ($item) { return clone $item; };
		$this->traits = array_map($clone, $this->traits);
		$this->cases = array_map($clone, $this->cases);
		$this->consts = array_map($clone, $this->consts);
		$this->properties = array_map($clone, $this->properties);
		$this->methods = array_map($clone, $this->methods);
	}
}
