<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\Provider\Dto\SplitDto;

final class SplitDtoFixture
{
	/** @api */
	public static function getSplitDto(?DateTimeImmutable $date = null, ?Decimal $factor = null): SplitDto
	{
		return new SplitDto(
			date: $date ?? new DateTimeImmutable(),
			factor: $factor ?? new Decimal(1),
		);
	}
}
