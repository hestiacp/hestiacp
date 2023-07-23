<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator\Traits;


/**
 * @internal
 */
trait CommentAware
{
	/** @var string|null */
	private $comment;


	/** @return static */
	public function setComment(?string $val): self
	{
		$this->comment = $val;
		return $this;
	}


	public function getComment(): ?string
	{
		return $this->comment;
	}


	/** @return static */
	public function addComment(string $val): self
	{
		$this->comment .= $this->comment ? "\n$val" : $val;
		return $this;
	}
}
