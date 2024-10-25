<?php

declare(strict_types=1);

namespace FinGather\Attribute;

use Attribute;
use Cycle\Annotated\Annotation\Column;
use FinGather\Utils\EnumUtils;
use Spiral\Attributes\NamedArgumentConstructor;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
//@phpstan-ignore-next-line
#[NamedArgumentConstructor]
class ColumnEnum extends Column
{
	/** @param class-string $enum */
	public function __construct(
		string $enum,
		?string $name = null,
		?string $property = null,
		bool $primary = false,
		bool $nullable = false,
		mixed $default = null,
		bool $castDefault = false,
		bool $readonlySchema = false,
	) {
		$type = EnumUtils::getEnumValuesString($enum);

		parent::__construct($type, $name, $property, $primary, $nullable, $default, $enum, $castDefault, $readonlySchema);
	}
}
