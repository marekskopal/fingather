<?php

declare(strict_types=1);

namespace FinGather\Service\Provider\Dto;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Split;

final readonly class SplitDto
{
	public function __construct(public DateTimeImmutable $date, public Decimal $factor)
	{
	}

	public static function fromEntity(Split $split): self
	{
		return new self(
			date: $split->getDate(),
			factor: $split->getFactor(),
		);
	}
}
