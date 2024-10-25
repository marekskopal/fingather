<?php

declare(strict_types=1);

namespace FinGather\Tests\Utils;

use FinGather\Model\Entity\Enum\TransactionCreateTypeEnum;
use FinGather\Utils\EnumUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnumUtils::class)]
final class EnumUtilsTest extends TestCase
{
	/**
	 * @param class-string $enumClass
	 * @param list<string> $expectedValues
	 */
	#[TestWith([TransactionCreateTypeEnum::class, ['Manual', 'Import']])]
	public function testGetEnumValues(string $enumClass, array $expectedValues): void
	{
		self::assertEquals($expectedValues, EnumUtils::getEnumValues($enumClass));
	}

	/** @param class-string $enumClass */
	#[TestWith([TransactionCreateTypeEnum::class, 'enum(Manual,Import)'])]
	public function testGetEnumValuesString(string $enumClass, string $expectedValues): void
	{
		self::assertEquals($expectedValues, EnumUtils::getEnumValuesString($enumClass));
	}
}
