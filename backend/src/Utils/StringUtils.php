<?php

declare(strict_types=1);

namespace FinGather\Utils;

use Nette\Utils\Strings;

final class StringUtils
{
	public static function sanitizeName(string $name): string
	{
		$name = Strings::capitalize($name);
		//remove multiple spaces
		$name = preg_replace('/\s+/', ' ', $name);
		//fix hyphens
		$name = str_replace('—', '-', $name);
		//fix spaces around hyphens
		$name = str_replace(' - ', '-', $name);
		$name = str_replace('-', ' - ', $name);

		return $name;
	}
}
