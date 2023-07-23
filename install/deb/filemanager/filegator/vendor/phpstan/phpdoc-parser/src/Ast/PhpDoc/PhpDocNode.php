<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\Node;

class PhpDocNode implements Node
{

	/** @var PhpDocChildNode[] */
	public $children;

	/**
	 * @param PhpDocChildNode[] $children
	 */
	public function __construct(array $children)
	{
		$this->children = $children;
	}


	/**
	 * @return PhpDocTagNode[]
	 */
	public function getTags(): array
	{
		return array_filter($this->children, static function (PhpDocChildNode $child): bool {
			return $child instanceof PhpDocTagNode;
		});
	}


	/**
	 * @param  string $tagName
	 * @return PhpDocTagNode[]
	 */
	public function getTagsByName(string $tagName): array
	{
		return array_filter($this->getTags(), static function (PhpDocTagNode $tag) use ($tagName): bool {
			return $tag->name === $tagName;
		});
	}


	/**
	 * @return VarTagValueNode[]
	 */
	public function getVarTagValues(): array
	{
		return array_column(
			array_filter($this->getTagsByName('@var'), static function (PhpDocTagNode $tag): bool {
				return $tag->value instanceof VarTagValueNode;
			}),
			'value'
		);
	}


	/**
	 * @return ParamTagValueNode[]
	 */
	public function getParamTagValues(): array
	{
		return array_column(
			array_filter($this->getTagsByName('@param'), static function (PhpDocTagNode $tag): bool {
				return $tag->value instanceof ParamTagValueNode;
			}),
			'value'
		);
	}


	/**
	 * @return TemplateTagValueNode[]
	 */
	public function getTemplateTagValues(): array
	{
		return array_column(
			array_filter($this->getTagsByName('@template'), static function (PhpDocTagNode $tag): bool {
				return $tag->value instanceof TemplateTagValueNode;
			}),
			'value'
		);
	}


	/**
	 * @return ReturnTagValueNode[]
	 */
	public function getReturnTagValues(): array
	{
		return array_column(
			array_filter($this->getTagsByName('@return'), static function (PhpDocTagNode $tag): bool {
				return $tag->value instanceof ReturnTagValueNode;
			}),
			'value'
		);
	}


	/**
	 * @return ThrowsTagValueNode[]
	 */
	public function getThrowsTagValues(): array
	{
		return array_column(
			array_filter($this->getTagsByName('@throws'), static function (PhpDocTagNode $tag): bool {
				return $tag->value instanceof ThrowsTagValueNode;
			}),
			'value'
		);
	}


	/**
	 * @return \PHPStan\PhpDocParser\Ast\PhpDoc\DeprecatedTagValueNode[]
	 */
	public function getDeprecatedTagValues(): array
	{
		return array_column(
			array_filter($this->getTagsByName('@deprecated'), static function (PhpDocTagNode $tag): bool {
				return $tag->value instanceof DeprecatedTagValueNode;
			}),
			'value'
		);
	}


	/**
	 * @return PropertyTagValueNode[]
	 */
	public function getPropertyTagValues(): array
	{
		return array_column(
			array_filter($this->getTagsByName('@property'), static function (PhpDocTagNode $tag): bool {
				return $tag->value instanceof PropertyTagValueNode;
			}),
			'value'
		);
	}


	/**
	 * @return PropertyTagValueNode[]
	 */
	public function getPropertyReadTagValues(): array
	{
		return array_column(
			array_filter($this->getTagsByName('@property-read'), static function (PhpDocTagNode $tag): bool {
				return $tag->value instanceof PropertyTagValueNode;
			}),
			'value'
		);
	}


	/**
	 * @return PropertyTagValueNode[]
	 */
	public function getPropertyWriteTagValues(): array
	{
		return array_column(
			array_filter($this->getTagsByName('@property-write'), static function (PhpDocTagNode $tag): bool {
				return $tag->value instanceof PropertyTagValueNode;
			}),
			'value'
		);
	}


	/**
	 * @return MethodTagValueNode[]
	 */
	public function getMethodTagValues(): array
	{
		return array_column(
			array_filter($this->getTagsByName('@method'), static function (PhpDocTagNode $tag): bool {
				return $tag->value instanceof MethodTagValueNode;
			}),
			'value'
		);
	}


	public function __toString(): string
	{
		return "/**\n * " . implode("\n * ", $this->children) . '*/';
	}

}
