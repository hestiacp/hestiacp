<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator\Traits;

use Nette;
use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\Parameter;
use Nette\Utils\Type;


/**
 * @internal
 */
trait FunctionLike
{
	/** @var string */
	private $body = '';

	/** @var Parameter[] */
	private $parameters = [];

	/** @var bool */
	private $variadic = false;

	/** @var string|null */
	private $returnType;

	/** @var bool */
	private $returnReference = false;

	/** @var bool */
	private $returnNullable = false;


	/** @return static */
	public function setBody(string $code, ?array $args = null): self
	{
		$this->body = $args === null
			? $code
			: (new Dumper)->format($code, ...$args);
		return $this;
	}


	public function getBody(): string
	{
		return $this->body;
	}


	/** @return static */
	public function addBody(string $code, ?array $args = null): self
	{
		$this->body .= ($args === null ? $code : (new Dumper)->format($code, ...$args)) . "\n";
		return $this;
	}


	/**
	 * @param  Parameter[]  $val
	 * @return static
	 */
	public function setParameters(array $val): self
	{
		(function (Parameter ...$val) {})(...array_values($val));
		$this->parameters = [];
		foreach ($val as $v) {
			$this->parameters[$v->getName()] = $v;
		}

		return $this;
	}


	/** @return Parameter[] */
	public function getParameters(): array
	{
		return $this->parameters;
	}


	/**
	 * @param  string  $name without $
	 */
	public function addParameter(string $name, $defaultValue = null): Parameter
	{
		$param = new Parameter($name);
		if (func_num_args() > 1) {
			$param->setDefaultValue($defaultValue);
		}

		return $this->parameters[$name] = $param;
	}


	/**
	 * @param  string  $name without $
	 * @return static
	 */
	public function removeParameter(string $name): self
	{
		unset($this->parameters[$name]);
		return $this;
	}


	/** @return static */
	public function setVariadic(bool $state = true): self
	{
		$this->variadic = $state;
		return $this;
	}


	public function isVariadic(): bool
	{
		return $this->variadic;
	}


	/** @return static */
	public function setReturnType(?string $type): self
	{
		$this->returnType = Nette\PhpGenerator\Helpers::validateType($type, $this->returnNullable);
		return $this;
	}


	/**
	 * @return Type|string|null
	 */
	public function getReturnType(bool $asObject = false)
	{
		return $asObject && $this->returnType
			? Type::fromString($this->returnType)
			: $this->returnType;
	}


	/** @return static */
	public function setReturnReference(bool $state = true): self
	{
		$this->returnReference = $state;
		return $this;
	}


	public function getReturnReference(): bool
	{
		return $this->returnReference;
	}


	/** @return static */
	public function setReturnNullable(bool $state = true): self
	{
		$this->returnNullable = $state;
		return $this;
	}


	public function isReturnNullable(): bool
	{
		return $this->returnNullable;
	}


	/** @deprecated  use isReturnNullable() */
	public function getReturnNullable(): bool
	{
		return $this->returnNullable;
	}


	/** @deprecated */
	public function setNamespace(?Nette\PhpGenerator\PhpNamespace $val = null): self
	{
		trigger_error(__METHOD__ . '() is deprecated', E_USER_DEPRECATED);
		return $this;
	}
}
