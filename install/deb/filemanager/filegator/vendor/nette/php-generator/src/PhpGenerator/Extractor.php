<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;
use PhpParser;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;


/**
 * Extracts information from PHP code.
 * @internal
 */
final class Extractor
{
	use Nette\SmartObject;

	private $code;
	private $statements;
	private $printer;


	public function __construct(string $code)
	{
		if (!class_exists(ParserFactory::class)) {
			throw new Nette\NotSupportedException("PHP-Parser is required to load method bodies, install package 'nikic/php-parser' 4.7 or newer.");
		}

		$this->printer = new PhpParser\PrettyPrinter\Standard;
		$this->parseCode($code);
	}


	private function parseCode(string $code): void
	{
		if (substr($code, 0, 5) !== '<?php') {
			throw new Nette\InvalidStateException('The input string is not a PHP code.');
		}

		$this->code = str_replace("\r\n", "\n", $code);
		$lexer = new PhpParser\Lexer\Emulative(['usedAttributes' => ['startFilePos', 'endFilePos', 'comments']]);
		$parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7, $lexer);
		$stmts = $parser->parse($this->code);

		$traverser = new PhpParser\NodeTraverser;
		$traverser->addVisitor(new PhpParser\NodeVisitor\ParentConnectingVisitor);
		$traverser->addVisitor(new PhpParser\NodeVisitor\NameResolver(null, ['preserveOriginalNames' => true]));
		$this->statements = $traverser->traverse($stmts);
	}


	public function extractMethodBodies(string $className): array
	{
		$nodeFinder = new NodeFinder;
		$classNode = $nodeFinder->findFirst($this->statements, function (Node $node) use ($className) {
			return $node instanceof Node\Stmt\ClassLike && $node->namespacedName->toString() === $className;
		});

		$res = [];
		foreach ($nodeFinder->findInstanceOf($classNode, Node\Stmt\ClassMethod::class) as $methodNode) {
			/** @var Node\Stmt\ClassMethod $methodNode */
			if ($methodNode->stmts) {
				$res[$methodNode->name->toString()] = $this->getReformattedContents($methodNode->stmts, 2);
			}
		}

		return $res;
	}


	public function extractFunctionBody(string $name): ?string
	{
		/** @var Node\Stmt\Function_ $functionNode */
		$functionNode = (new NodeFinder)->findFirst($this->statements, function (Node $node) use ($name) {
			return $node instanceof Node\Stmt\Function_ && $node->namespacedName->toString() === $name;
		});

		return $this->getReformattedContents($functionNode->stmts, 1);
	}


	/** @param  Node[]  $statements */
	private function getReformattedContents(array $statements, int $level): string
	{
		$body = $this->getNodeContents(...$statements);
		$body = $this->performReplacements($body, $this->prepareReplacements($statements));
		return Helpers::unindent($body, $level);
	}


	private function prepareReplacements(array $statements): array
	{
		$start = $this->getNodeStartPos($statements[0]);
		$replacements = [];
		(new NodeFinder)->find($statements, function (Node $node) use (&$replacements, $start) {
			if ($node instanceof Node\Name\FullyQualified) {
				if ($node->getAttribute('originalName') instanceof Node\Name) {
					$of = $node->getAttribute('parent') instanceof Node\Expr\ConstFetch
							? PhpNamespace::NameConstant
							: ($node->getAttribute('parent') instanceof Node\Expr\FuncCall ? PhpNamespace::NameFunction : PhpNamespace::NameNormal);
					$replacements[] = [
						$node->getStartFilePos() - $start,
						$node->getEndFilePos() - $start,
						Helpers::tagName($node->toCodeString(), $of),
					];
				}
			} elseif ($node instanceof Node\Scalar\String_ || $node instanceof Node\Scalar\EncapsedStringPart) {
				// multi-line strings => singleline
				$token = $this->getNodeContents($node);
				if (strpos($token, "\n") !== false) {
					$quote = $node instanceof Node\Scalar\String_ ? '"' : '';
					$replacements[] = [
						$node->getStartFilePos() - $start,
						$node->getEndFilePos() - $start,
						$quote . addcslashes($node->value, "\x00..\x1F") . $quote,
					];
				}
			} elseif ($node instanceof Node\Scalar\Encapsed) {
				// HEREDOC => "string"
				if ($node->getAttribute('kind') === Node\Scalar\String_::KIND_HEREDOC) {
					$replacements[] = [
						$node->getStartFilePos() - $start,
						$node->parts[0]->getStartFilePos() - $start - 1,
						'"',
					];
					$replacements[] = [
						end($node->parts)->getEndFilePos() - $start + 1,
						$node->getEndFilePos() - $start,
						'"',
					];
				}
			}
		});
		return $replacements;
	}


	private function performReplacements(string $s, array $replacements): string
	{
		usort($replacements, function ($a, $b) { // sort by position in file
			return $b[0] <=> $a[0];
		});

		foreach ($replacements as [$start, $end, $replacement]) {
			$s = substr_replace($s, $replacement, $start, $end - $start + 1);
		}

		return $s;
	}


	public function extractAll(): PhpFile
	{
		$phpFile = new PhpFile;
		$namespace = '';
		$visitor = new class extends PhpParser\NodeVisitorAbstract {
			public $callback;


			public function enterNode(Node $node)
			{
				return ($this->callback)($node);
			}
		};

		$visitor->callback = function (Node $node) use (&$class, &$namespace, $phpFile) {
			if ($node instanceof Node\Stmt\DeclareDeclare && $node->key->name === 'strict_types') {
				$phpFile->setStrictTypes((bool) $node->value->value);
			} elseif ($node instanceof Node\Stmt\Namespace_) {
				$namespace = $node->name ? $node->name->toString() : '';
			} elseif ($node instanceof Node\Stmt\Use_) {
				$this->addUseToNamespace($node, $phpFile->addNamespace($namespace));
			} elseif ($node instanceof Node\Stmt\Class_) {
				if (!$node->name) {
					return PhpParser\NodeTraverser::DONT_TRAVERSE_CHILDREN;
				}

				$class = $this->addClassToFile($phpFile, $node);
			} elseif ($node instanceof Node\Stmt\Interface_) {
				$class = $this->addInterfaceToFile($phpFile, $node);
			} elseif ($node instanceof Node\Stmt\Trait_) {
				$class = $this->addTraitToFile($phpFile, $node);
			} elseif ($node instanceof Node\Stmt\Enum_) {
				$class = $this->addEnumToFile($phpFile, $node);
			} elseif ($node instanceof Node\Stmt\Function_) {
				$this->addFunctionToFile($phpFile, $node);
			} elseif ($node instanceof Node\Stmt\TraitUse) {
				$this->addTraitToClass($class, $node);
			} elseif ($node instanceof Node\Stmt\Property) {
				$this->addPropertyToClass($class, $node);
			} elseif ($node instanceof Node\Stmt\ClassMethod) {
				$this->addMethodToClass($class, $node);
			} elseif ($node instanceof Node\Stmt\ClassConst) {
				$this->addConstantToClass($class, $node);
			} elseif ($node instanceof Node\Stmt\EnumCase) {
				$this->addEnumCaseToClass($class, $node);
			}

			if ($node instanceof Node\FunctionLike) {
				return PhpParser\NodeTraverser::DONT_TRAVERSE_CHILDREN;
			}
		};

		if ($this->statements) {
			$this->addCommentAndAttributes($phpFile, $this->statements[0]);
		}

		$traverser = new PhpParser\NodeTraverser;
		$traverser->addVisitor($visitor);
		$traverser->traverse($this->statements);
		return $phpFile;
	}


	private function addUseToNamespace(Node\Stmt\Use_ $node, PhpNamespace $namespace): void
	{
		$of = [
			$node::TYPE_NORMAL => PhpNamespace::NameNormal,
			$node::TYPE_FUNCTION => PhpNamespace::NameFunction,
			$node::TYPE_CONSTANT => PhpNamespace::NameConstant,
		][$node->type];
		foreach ($node->uses as $use) {
			$namespace->addUse($use->name->toString(), $use->alias ? $use->alias->toString() : null, $of);
		}
	}


	private function addClassToFile(PhpFile $phpFile, Node\Stmt\Class_ $node): ClassType
	{
		$class = $phpFile->addClass($node->namespacedName->toString());
		if ($node->extends) {
			$class->setExtends($node->extends->toString());
		}

		foreach ($node->implements as $item) {
			$class->addImplement($item->toString());
		}

		$class->setFinal($node->isFinal());
		$class->setAbstract($node->isAbstract());
		$this->addCommentAndAttributes($class, $node);
		return $class;
	}


	private function addInterfaceToFile(PhpFile $phpFile, Node\Stmt\Interface_ $node): ClassType
	{
		$class = $phpFile->addInterface($node->namespacedName->toString());
		foreach ($node->extends as $item) {
			$class->addExtend($item->toString());
		}

		$this->addCommentAndAttributes($class, $node);
		return $class;
	}


	private function addTraitToFile(PhpFile $phpFile, Node\Stmt\Trait_ $node): ClassType
	{
		$class = $phpFile->addTrait($node->namespacedName->toString());
		$this->addCommentAndAttributes($class, $node);
		return $class;
	}


	private function addEnumToFile(PhpFile $phpFile, Node\Stmt\Enum_ $node): ClassType
	{
		$class = $phpFile->addEnum($node->namespacedName->toString());
		foreach ($node->implements as $item) {
			$class->addImplement($item->toString());
		}

		$this->addCommentAndAttributes($class, $node);
		return $class;
	}


	private function addFunctionToFile(PhpFile $phpFile, Node\Stmt\Function_ $node): void
	{
		$function = $phpFile->addFunction($node->namespacedName->toString());
		$this->setupFunction($function, $node);
	}


	private function addTraitToClass(ClassType $class, Node\Stmt\TraitUse $node): void
	{
		foreach ($node->traits as $item) {
			$trait = $class->addTrait($item->toString(), true);
		}

		foreach ($node->adaptations as $item) {
			$trait->addResolution(trim($this->toPhp($item), ';'));
		}

		$this->addCommentAndAttributes($trait, $node);
	}


	private function addPropertyToClass(ClassType $class, Node\Stmt\Property $node): void
	{
		foreach ($node->props as $item) {
			$prop = $class->addProperty($item->name->toString());
			$prop->setStatic($node->isStatic());
			$prop->setVisibility($this->toVisibility($node->flags));
			$prop->setType($node->type ? $this->toPhp($node->type) : null);
			if ($item->default) {
				$prop->setValue(new Literal($this->getReformattedContents([$item->default], 1)));
			}

			$prop->setReadOnly(method_exists($node, 'isReadonly') && $node->isReadonly());
			$this->addCommentAndAttributes($prop, $node);
		}
	}


	private function addMethodToClass(ClassType $class, Node\Stmt\ClassMethod $node): void
	{
		$method = $class->addMethod($node->name->toString());
		$method->setAbstract($node->isAbstract());
		$method->setFinal($node->isFinal());
		$method->setStatic($node->isStatic());
		$method->setVisibility($this->toVisibility($node->flags));
		$this->setupFunction($method, $node);
	}


	private function addConstantToClass(ClassType $class, Node\Stmt\ClassConst $node): void
	{
		foreach ($node->consts as $item) {
			$value = $this->getReformattedContents([$item->value], 1);
			$const = $class->addConstant($item->name->toString(), new Literal($value));
			$const->setVisibility($this->toVisibility($node->flags));
			$const->setFinal(method_exists($node, 'isFinal') && $node->isFinal());
			$this->addCommentAndAttributes($const, $node);
		}
	}


	private function addEnumCaseToClass(ClassType $class, Node\Stmt\EnumCase $node)
	{
		$case = $class->addCase($node->name->toString(), $node->expr ? $node->expr->value : null);
		$this->addCommentAndAttributes($case, $node);
	}


	private function addCommentAndAttributes($element, Node $node): void
	{
		if ($node->getDocComment()) {
			$comment = $node->getDocComment()->getReformattedText();
			$comment = Helpers::unformatDocComment($comment);
			$element->setComment($comment);
			$node->setDocComment(new PhpParser\Comment\Doc(''));
		}

		foreach ($node->attrGroups ?? [] as $group) {
			foreach ($group->attrs as $attribute) {
				$args = [];
				foreach ($attribute->args as $arg) {
					$value = new Literal($this->getReformattedContents([$arg->value], 0));
					if ($arg->name) {
						$args[$arg->name->toString()] = $value;
					} else {
						$args[] = $value;
					}
				}

				$element->addAttribute($attribute->name->toString(), $args);
			}
		}
	}


	/**
	 * @param  GlobalFunction|Method  $function
	 */
	private function setupFunction($function, Node\FunctionLike $node): void
	{
		$function->setReturnReference($node->returnsByRef());
		$function->setReturnType($node->getReturnType() ? $this->toPhp($node->getReturnType()) : null);
		foreach ($node->getParams() as $item) {
			$visibility = $this->toVisibility($item->flags);
			$isReadonly = (bool) ($item->flags & Node\Stmt\Class_::MODIFIER_READONLY);
			$param = $visibility
				? ($function->addPromotedParameter($item->var->name))->setVisibility($visibility)->setReadonly($isReadonly)
				: $function->addParameter($item->var->name);
			$param->setType($item->type ? $this->toPhp($item->type) : null);
			$param->setReference($item->byRef);
			$function->setVariadic($item->variadic);
			if ($item->default) {
				$param->setDefaultValue(new Literal($this->getReformattedContents([$item->default], 2)));
			}

			$this->addCommentAndAttributes($param, $item);
		}

		$this->addCommentAndAttributes($function, $node);
		if ($node->getStmts()) {
			$function->setBody($this->getReformattedContents($node->getStmts(), 2));
		}
	}


	private function toVisibility(int $flags): ?string
	{
		if ($flags & Node\Stmt\Class_::MODIFIER_PUBLIC) {
			return ClassType::VisibilityPublic;
		} elseif ($flags & Node\Stmt\Class_::MODIFIER_PROTECTED) {
			return ClassType::VisibilityProtected;
		} elseif ($flags & Node\Stmt\Class_::MODIFIER_PRIVATE) {
			return ClassType::VisibilityPrivate;
		}
		return null;
	}


	private function toPhp($value): string
	{
		return $this->printer->prettyPrint([$value]);
	}


	private function getNodeContents(Node ...$nodes): string
	{
		$start = $this->getNodeStartPos($nodes[0]);
		return substr($this->code, $start, end($nodes)->getEndFilePos() - $start + 1);
	}


	private function getNodeStartPos(Node $node): int
	{
		return ($comments = $node->getComments())
			? $comments[0]->getStartFilePos()
			: $node->getStartFilePos();
	}
}
