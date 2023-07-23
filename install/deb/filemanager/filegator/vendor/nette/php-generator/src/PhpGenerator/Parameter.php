<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;
use Nette\Utils\Type;


/**
 * Function/Method parameter description.
 *
 * @property mixed $defaultValue
 */
class Parameter
{
	use Nette\SmartObject;
	use Traits\NameAware;
	use Traits\AttributeAware;

	/** @var bool */
	private $reference = false;

	/** @var string|null */
	private $type;

	/** @var bool */
	private $nullable = false;

	/** @var bool */
	private $hasDefaultValue = false;

	/** @var mixed */
	private $defaultValue;


	/** @return static */
	public function setReference(bool $state = true): self
	{
		$this->reference = $state;
		return $this;
	}


	public function isReference(): bool
	{
		return $this->reference;
	}


	/** @return static */
	public function setType(?string $type): self
	{
		$this->type = Helpers::validateType($type, $this->nullable);
		return $this;
	}


	/**
	 * @return Type|string|null
	 */
	public function getType(bool $asObject = false)
	{
		return $asObject && $this->type
			? Type::fromString($this->type)
			: $this->type;
	}


	/** @deprecated  use setType() */
	public function setTypeHint(?string $type): self
	{
		return $this->setType($type);
	}


	/** @deprecated  use getType() */
	public function getTypeHint(): ?string
	{
		return $this->getType();
	}


	/**
	 * @deprecated  just use setDefaultValue()
	 * @return static
	 */
	public function setOptional(bool $state = true): self
	{
		trigger_error(__METHOD__ . '() is deprecated, use setDefaultValue()', E_USER_DEPRECATED);
		$this->hasDefaultValue = $state;
		return $this;
	}


	/** @return static */
	public function setNullable(bool $state = true): self
	{
		$this->nullable = $state;
		return $this;
	}


	public function isNullable(): bool
	{
		return $this->nullable;
	}


	/** @return static */
	public function setDefaultValue($val): self
	{
		$this->defaultValue = $val;
		$this->hasDefaultValue = true;
		return $this;
	}


	public function getDefaultValue()
	{
		return $this->defaultValue;
	}


	public function hasDefaultValue(): bool
	{
		return $this->hasDefaultValue;
	}


	public function validate(): void
	{
	}
}
