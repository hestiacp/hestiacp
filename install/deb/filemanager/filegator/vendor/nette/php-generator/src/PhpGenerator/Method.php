<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


/**
 * Class method.
 *
 * @property string|null $body
 */
final class Method
{
	use Nette\SmartObject;
	use Traits\FunctionLike;
	use Traits\NameAware;
	use Traits\VisibilityAware;
	use Traits\CommentAware;
	use Traits\AttributeAware;

	/** @var string|null */
	private $body = '';

	/** @var bool */
	private $static = false;

	/** @var bool */
	private $final = false;

	/** @var bool */
	private $abstract = false;


	/**
	 * @param  string|array  $method
	 */
	public static function from($method): self
	{
		return (new Factory)->fromMethodReflection(Nette\Utils\Callback::toReflection($method));
	}


	public function __toString(): string
	{
		try {
			return (new Printer)->printMethod($this);
		} catch (\Throwable $e) {
			if (PHP_VERSION_ID >= 70400) {
				throw $e;
			}

			trigger_error('Exception in ' . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", E_USER_ERROR);
			return '';
		}
	}


	/** @return static */
	public function setBody(?string $code, ?array $args = null): self
	{
		$this->body = $args === null || $code === null
			? $code
			: (new Dumper)->format($code, ...$args);
		return $this;
	}


	public function getBody(): ?string
	{
		return $this->body;
	}


	/** @return static */
	public function setStatic(bool $state = true): self
	{
		$this->static = $state;
		return $this;
	}


	public function isStatic(): bool
	{
		return $this->static;
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
	 * @param  string  $name without $
	 */
	public function addPromotedParameter(string $name, $defaultValue = null): PromotedParameter
	{
		$param = new PromotedParameter($name);
		if (func_num_args() > 1) {
			$param->setDefaultValue($defaultValue);
		}

		return $this->parameters[$name] = $param;
	}


	/** @throws Nette\InvalidStateException */
	public function validate(): void
	{
		if ($this->abstract && ($this->final || $this->visibility === ClassType::VisibilityPrivate)) {
			throw new Nette\InvalidStateException("Method $this->name() cannot be abstract and final or private at the same time.");
		}
	}
}
