<?php

declare(strict_types=1);

namespace FinGather\PhpStan;

use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Decimal\Attribute\ColumnDecimal;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;

class CycleReadWritePropertiesExtension implements ReadWritePropertiesExtension
{
	public function isAlwaysRead(PropertyReflection $property, string $propertyName): bool
	{
		//@phpstan-ignore-next-line
		if (!($property instanceof PhpPropertyReflection)) {
			return false;
		}

		$attributes = $property->getNativeReflection()->getAttributes();
		foreach ($attributes as $attribute) {
			if (in_array($attribute->getName(), [
				Column::class,
				ManyToOne::class,
				ColumnDecimal::class,
				ColumnEnum::class,
			], true)) {
				return true;
			}
		}

		return false;
	}

	public function isAlwaysWritten(PropertyReflection $property, string $propertyName): bool
	{
		return false;
	}

	public function isInitialized(PropertyReflection $property, string $propertyName): bool
	{
		return false;
	}
}
