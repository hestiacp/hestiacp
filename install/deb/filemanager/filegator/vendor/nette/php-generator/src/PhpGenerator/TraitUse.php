<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


/**
 * use Trait
 */
final class TraitUse
{
	use Nette\SmartObject;
	use Traits\NameAware;
	use Traits\CommentAware;

	/** @var array */
	private $resolutions = [];


	public function __construct(string $name)
	{
		if (!Nette\PhpGenerator\Helpers::isNamespaceIdentifier($name, true)) {
			throw new Nette\InvalidArgumentException("Value '$name' is not valid trait name.");
		}

		$this->name = $name;
	}


	public function addResolution(string $resolution): self
	{
		$this->resolutions[] = $resolution;
		return $this;
	}


	public function getResolutions(): array
	{
		return $this->resolutions;
	}
}
