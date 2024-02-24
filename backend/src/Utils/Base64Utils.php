<?php

declare(strict_types=1);

namespace FinGather\Utils;

use function Safe\base64_decode;

class Base64Utils
{
	/**
	 * @param list<string> $strings
	 * @return list<string>
	 */
	public static function encodeList(array $strings): array
	{
		return array_map(
			fn (string $item): string => base64_encode($item),
			$strings,
		);
	}

	/**
	 * @param list<string> $base64Strings
	 * @return list<string>
	 */
	public static function decodeList(array $base64Strings): array
	{
		return array_map(
			fn (string $item): string => base64_decode(
				strpos($item, 'base64,') !== false ? substr($item, strpos($item, 'base64,') + 7) : $item,
				strict: true,
			),
			$base64Strings,
		);
	}
}
