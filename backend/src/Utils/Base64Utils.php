<?php

declare(strict_types=1);

namespace FinGather\Utils;

final class Base64Utils
{
	public static function encode(string $string): string
	{
		return base64_encode($string);
	}

	public static function decode(string $string): string
	{
		return (string) base64_decode(
			strpos($string, 'base64,') !== false ? substr($string, strpos($string, 'base64,') + 7) : $string,
			strict: true,
		);
	}
}
