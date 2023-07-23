<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;
use Nette\Utils\Strings;


/**
 * Generates PHP code.
 */
class Printer
{
	use Nette\SmartObject;

	/** @var int */
	public $wrapLength = 120;

	/** @var string */
	protected $indentation = "\t";

	/** @var int */
	protected $linesBetweenProperties = 0;

	/** @var int */
	protected $linesBetweenMethods = 2;

	/** @var string */
	protected $returnTypeColon = ': ';

	/** @var ?PhpNamespace */
	protected $namespace;

	/** @var ?Dumper */
	protected $dumper;

	/** @var bool */
	private $resolveTypes = true;


	public function __construct()
	{
		$this->dumper = new Dumper;
	}


	public function printFunction(GlobalFunction $function, ?PhpNamespace $namespace = null): string
	{
		$this->namespace = $this->resolveTypes ? $namespace : null;
		$line = 'function '
			. ($function->getReturnReference() ? '&' : '')
			. $function->getName();
		$returnType = $this->printReturnType($function);
		$body = Helpers::simplifyTaggedNames($function->getBody(), $this->namespace);

		return Helpers::formatDocComment($function->getComment() . "\n")
			. $this->printAttributes($function->getAttributes())
			. $line
			. $this->printParameters($function, strlen($line) + strlen($returnType) + 2) // 2 = parentheses
			. $returnType
			. "\n{\n" . $this->indent(ltrim(rtrim($body) . "\n")) . "}\n";
	}


	public function printClosure(Closure $closure, ?PhpNamespace $namespace = null): string
	{
		$this->namespace = $this->resolveTypes ? $namespace : null;
		$uses = [];
		foreach ($closure->getUses() as $param) {
			$uses[] = ($param->isReference() ? '&' : '') . '$' . $param->getName();
		}

		$useStr = strlen($tmp = implode(', ', $uses)) > $this->wrapLength && count($uses) > 1
			? "\n" . $this->indentation . implode(",\n" . $this->indentation, $uses) . "\n"
			: $tmp;
		$body = Helpers::simplifyTaggedNames($closure->getBody(), $this->namespace);

		return $this->printAttributes($closure->getAttributes(), true)
			. 'function '
			. ($closure->getReturnReference() ? '&' : '')
			. $this->printParameters($closure)
			. ($uses ? " use ($useStr)" : '')
			. $this->printReturnType($closure)
			. " {\n" . $this->indent(ltrim(rtrim($body) . "\n")) . '}';
	}


	public function printArrowFunction(Closure $closure, ?PhpNamespace $namespace = null): string
	{
		$this->namespace = $this->resolveTypes ? $namespace : null;
		foreach ($closure->getUses() as $use) {
			if ($use->isReference()) {
				throw new Nette\InvalidArgumentException('Arrow function cannot bind variables by-reference.');
			}
		}

		$body = Helpers::simplifyTaggedNames($closure->getBody(), $this->namespace);

		return $this->printAttributes($closure->getAttributes())
			. 'fn'
			. ($closure->getReturnReference() ? '&' : '')
			. $this->printParameters($closure)
			. $this->printReturnType($closure)
			. ' => ' . trim($body) . ';';
	}


	public function printMethod(Method $method, ?PhpNamespace $namespace = null): string
	{
		$this->namespace = $this->resolveTypes ? $namespace : null;
		$method->validate();
		$line = ($method->isAbstract() ? 'abstract ' : '')
			. ($method->isFinal() ? 'final ' : '')
			. ($method->getVisibility() ? $method->getVisibility() . ' ' : '')
			. ($method->isStatic() ? 'static ' : '')
			. 'function '
			. ($method->getReturnReference() ? '&' : '')
			. $method->getName();
		$returnType = $this->printReturnType($method);
		$params = $this->printParameters($method, strlen($line) + strlen($returnType) + strlen($this->indentation) + 2);
		$body = Helpers::simplifyTaggedNames((string) $method->getBody(), $this->namespace);

		return Helpers::formatDocComment($method->getComment() . "\n")
			. $this->printAttributes($method->getAttributes())
			. $line
			. $params
			. $returnType
			. ($method->isAbstract() || $method->getBody() === null
				? ";\n"
				: (strpos($params, "\n") === false ? "\n" : ' ')
					. "{\n"
					. $this->indent(ltrim(rtrim($body) . "\n"))
					. "}\n");
	}


	public function printClass(ClassType $class, ?PhpNamespace $namespace = null): string
	{
		$this->namespace = $this->resolveTypes ? $namespace : null;
		$class->validate();
		$resolver = $this->namespace
			? [$namespace, 'simplifyType']
			: function ($s) { return $s; };

		$traits = [];
		foreach ($class->getTraitResolutions() as $trait) {
			$resolutions = $trait->getResolutions();
			$traits[] = Helpers::formatDocComment((string) $trait->getComment())
				. 'use ' . $resolver($trait->getName())
				. ($resolutions
					? " {\n" . $this->indentation . implode(";\n" . $this->indentation, $resolutions) . ";\n}\n"
					: ";\n");
		}

		$cases = [];
		foreach ($class->getCases() as $case) {
			$cases[] = Helpers::formatDocComment((string) $case->getComment())
				. $this->printAttributes($case->getAttributes())
				. 'case ' . $case->getName()
				. ($case->getValue() === null ? '' : ' = ' . $this->dump($case->getValue()))
				. ";\n";
		}

		$enumType = isset($case) && $case->getValue() !== null
			? $this->returnTypeColon . Type::getType($case->getValue())
			: '';

		$consts = [];
		foreach ($class->getConstants() as $const) {
			$def = ($const->isFinal() ? 'final ' : '')
				. ($const->getVisibility() ? $const->getVisibility() . ' ' : '')
				. 'const ' . $const->getName() . ' = ';

			$consts[] = Helpers::formatDocComment((string) $const->getComment())
				. $this->printAttributes($const->getAttributes())
				. $def
				. $this->dump($const->getValue(), strlen($def)) . ";\n";
		}

		$properties = [];
		foreach ($class->getProperties() as $property) {
			$property->validate();
			$type = $property->getType();
			$def = (($property->getVisibility() ?: 'public')
				. ($property->isStatic() ? ' static' : '')
				. ($property->isReadOnly() && $type ? ' readonly' : '')
				. ' '
				. ltrim($this->printType($type, $property->isNullable()) . ' ')
				. '$' . $property->getName());

			$properties[] = Helpers::formatDocComment((string) $property->getComment())
				. $this->printAttributes($property->getAttributes())
				. $def
				. ($property->getValue() === null && !$property->isInitialized()
					? ''
					: ' = ' . $this->dump($property->getValue(), strlen($def) + 3)) // 3 = ' = '
				. ";\n";
		}

		$methods = [];
		foreach ($class->getMethods() as $method) {
			$methods[] = $this->printMethod($method, $namespace);
		}

		$members = array_filter([
			implode('', $traits),
			$this->joinProperties($cases),
			$this->joinProperties($consts),
			$this->joinProperties($properties),
			($methods && $properties ? str_repeat("\n", $this->linesBetweenMethods - 1) : '')
			. implode(str_repeat("\n", $this->linesBetweenMethods), $methods),
		]);

		return Strings::normalize(
			Helpers::formatDocComment($class->getComment() . "\n")
			. $this->printAttributes($class->getAttributes())
			. ($class->isAbstract() ? 'abstract ' : '')
			. ($class->isFinal() ? 'final ' : '')
			. ($class->getName() ? $class->getType() . ' ' . $class->getName() . $enumType . ' ' : '')
			. ($class->getExtends() ? 'extends ' . implode(', ', array_map($resolver, (array) $class->getExtends())) . ' ' : '')
			. ($class->getImplements() ? 'implements ' . implode(', ', array_map($resolver, $class->getImplements())) . ' ' : '')
			. ($class->getName() ? "\n" : '') . "{\n"
			. ($members ? $this->indent(implode("\n", $members)) : '')
			. '}'
		) . ($class->getName() ? "\n" : '');
	}


	public function printNamespace(PhpNamespace $namespace): string
	{
		$this->namespace = $this->resolveTypes ? $namespace : null;
		$name = $namespace->getName();
		$uses = $this->printUses($namespace)
			. $this->printUses($namespace, PhpNamespace::NameFunction)
			. $this->printUses($namespace, PhpNamespace::NameConstant);

		$items = [];
		foreach ($namespace->getClasses() as $class) {
			$items[] = $this->printClass($class, $namespace);
		}

		foreach ($namespace->getFunctions() as $function) {
			$items[] = $this->printFunction($function, $namespace);
		}

		$body = ($uses ? $uses . "\n" : '')
			. implode("\n", $items);

		if ($namespace->hasBracketedSyntax()) {
			return 'namespace' . ($name ? " $name" : '') . "\n{\n"
				. $this->indent($body)
				. "}\n";

		} else {
			return ($name ? "namespace $name;\n\n" : '')
				. $body;
		}
	}


	public function printFile(PhpFile $file): string
	{
		$namespaces = [];
		foreach ($file->getNamespaces() as $namespace) {
			$namespaces[] = $this->printNamespace($namespace);
		}

		return Strings::normalize(
			"<?php\n"
			. ($file->getComment() ? "\n" . Helpers::formatDocComment($file->getComment() . "\n") : '')
			. "\n"
			. ($file->hasStrictTypes() ? "declare(strict_types=1);\n\n" : '')
			. implode("\n\n", $namespaces)
		) . "\n";
	}


	protected function printUses(PhpNamespace $namespace, string $of = PhpNamespace::NameNormal): string
	{
		$prefix = [
			PhpNamespace::NameNormal => '',
			PhpNamespace::NameFunction => 'function ',
			PhpNamespace::NameConstant => 'const ',
		][$of];
		$name = $namespace->getName();
		$uses = [];
		foreach ($namespace->getUses($of) as $alias => $original) {
			$uses[] = Helpers::extractShortName($original) === $alias
				? "use $prefix$original;\n"
				: "use $prefix$original as $alias;\n";
		}

		return implode('', $uses);
	}


	/**
	 * @param Closure|GlobalFunction|Method  $function
	 */
	protected function printParameters($function, int $column = 0): string
	{
		$params = [];
		$list = $function->getParameters();
		$special = false;

		foreach ($list as $param) {
			$param->validate();
			$variadic = $function->isVariadic() && $param === end($list);
			$type = $param->getType();
			$promoted = $param instanceof PromotedParameter ? $param : null;
			$params[] =
				($promoted ? Helpers::formatDocComment((string) $promoted->getComment()) : '')
				. ($attrs = $this->printAttributes($param->getAttributes(), true))
				. ($promoted ?
					($promoted->getVisibility() ?: 'public')
					. ($promoted->isReadOnly() && $type ? ' readonly' : '')
					. ' ' : '')
				. ltrim($this->printType($type, $param->isNullable()) . ' ')
				. ($param->isReference() ? '&' : '')
				. ($variadic ? '...' : '')
				. '$' . $param->getName()
				. ($param->hasDefaultValue() && !$variadic ? ' = ' . $this->dump($param->getDefaultValue()) : '');

			$special = $special || $promoted || $attrs;
		}

		$line = implode(', ', $params);

		return count($params) > 1 && ($special || strlen($line) + $column > $this->wrapLength)
			? "(\n" . $this->indent(implode(",\n", $params)) . ($special ? ',' : '') . "\n)"
			: "($line)";
	}


	protected function printType(?string $type, bool $nullable): string
	{
		if ($type === null) {
			return '';
		}

		if ($this->namespace) {
			$type = $this->namespace->simplifyType($type);
		}

		if ($nullable && strcasecmp($type, 'mixed')) {
			$type = strpos($type, '|') === false
				? '?' . $type
				: $type . '|null';
		}

		return $type;
	}


	/**
	 * @param Closure|GlobalFunction|Method  $function
	 */
	private function printReturnType($function): string
	{
		return ($tmp = $this->printType($function->getReturnType(), $function->isReturnNullable()))
			? $this->returnTypeColon . $tmp
			: '';
	}


	private function printAttributes(array $attrs, bool $inline = false): string
	{
		if (!$attrs) {
			return '';
		}

		$this->dumper->indentation = $this->indentation;
		$items = [];
		foreach ($attrs as $attr) {
			$args = $this->dumper->format('...?:', $attr->getArguments());
			$args = Helpers::simplifyTaggedNames($args, $this->namespace);
			$items[] = $this->printType($attr->getName(), false) . ($args ? "($args)" : '');
		}

		return $inline
			? '#[' . implode(', ', $items) . '] '
			: '#[' . implode("]\n#[", $items) . "]\n";
	}


	/** @return static */
	public function setTypeResolving(bool $state = true): self
	{
		$this->resolveTypes = $state;
		return $this;
	}


	protected function indent(string $s): string
	{
		$s = str_replace("\t", $this->indentation, $s);
		return Strings::indent($s, 1, $this->indentation);
	}


	protected function dump($var, int $column = 0): string
	{
		$this->dumper->indentation = $this->indentation;
		$this->dumper->wrapLength = $this->wrapLength;
		$s = $this->dumper->dump($var, $column);
		$s = Helpers::simplifyTaggedNames($s, $this->namespace);
		return $s;
	}


	private function joinProperties(array $props): string
	{
		return $this->linesBetweenProperties
			? implode(str_repeat("\n", $this->linesBetweenProperties), $props)
			: preg_replace('#^(\w.*\n)\n(?=\w.*;)#m', '$1', implode("\n", $props));
	}
}
