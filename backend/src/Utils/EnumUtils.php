<?php

declare(strict_types=1);

namespace FinGather\Utils;

enum EnumUtils
{
	/**
	 * @param class-string<T> $enumClass
	 * @return array<string>
	 * @template T
	 * @api
	 */
	public static function getEnumValues(string $enumClass): array
	{
		return array_column($enumClass::cases(), 'value');
	}

	/**
	 * @param class-string<T> $enumClass
	 * @return non-empty-string
	 * @template T
	 */
	public static function getEnumValuesString(string $enumClass): string
	{
		return 'enum(' . implode(',', self::getEnumValues($enumClass)) . ')';
	}
}
