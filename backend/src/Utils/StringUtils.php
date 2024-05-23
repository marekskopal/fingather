<?php

declare(strict_types=1);

namespace FinGather\Utils;

use Nette\Utils\Strings;

final class StringUtils
{
	public static function sanitizeName(string $name): string
	{
		$name = Strings::capitalize($name);
		$name = str_replace('—', '-', $name);

		return $name;
	}
}
