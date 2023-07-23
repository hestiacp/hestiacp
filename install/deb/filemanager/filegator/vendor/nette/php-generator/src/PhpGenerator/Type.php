<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;


/**
 * PHP return, property and parameter types.
 */
class Type
{
	public const
		String = 'string',
		Int = 'int',
		Float = 'float',
		Bool = 'bool',
		Array = 'array',
		Object = 'object',
		Callable = 'callable',
		Iterable = 'iterable',
		Void = 'void',
		Never = 'never',
		Mixed = 'mixed',
		False = 'false',
		Null = 'null',
		Self = 'self',
		Parent = 'parent',
		Static = 'static';

	/** @deprecated */
	public const
		STRING = self::String,
		INT = self::Int,
		FLOAT = self::Float,
		BOOL = self::Bool,
		ARRAY = self::Array,
		OBJECT = self::Object,
		CALLABLE = self::Callable,
		ITERABLE = self::Iterable,
		VOID = self::Void,
		NEVER = self::Never,
		MIXED = self::Mixed,
		FALSE = self::False,
		NULL = self::Null,
		SELF = self::Self,
		PARENT = self::Parent,
		STATIC = self::Static;


	public static function nullable(string $type, bool $state = true): string
	{
		return ($state ? '?' : '') . ltrim($type, '?');
	}


	public static function union(string ...$types): string
	{
		return implode('|', $types);
	}


	public static function intersection(string ...$types): string
	{
		return implode('&', $types);
	}


	public static function getType($value): ?string
	{
		if (is_object($value)) {
			return get_class($value);
		} elseif (is_int($value)) {
			return self::INT;
		} elseif (is_float($value)) {
			return self::FLOAT;
		} elseif (is_string($value)) {
			return self::STRING;
		} elseif (is_bool($value)) {
			return self::BOOL;
		} elseif (is_array($value)) {
			return self::ARRAY;
		} else {
			return null;
		}
	}
}
