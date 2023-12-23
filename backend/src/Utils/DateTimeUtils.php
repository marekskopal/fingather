<?php

declare(strict_types=1);

namespace FinGather\Utils;

use DateTime;
use DateTimeImmutable;

final class DateTimeUtils
{
	private const FORMAT_ZULU = 'Y-m-d\TH:i:sp';

	public static function formatZulu(DateTimeImmutable|DateTime $dateTime): string
	{
		return $dateTime->format(self::FORMAT_ZULU);
	}
}
