<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\BenchmarkData;
use FinGather\Utils\DateTimeUtils;

final readonly class BenchmarkDataDto
{
	public function __construct(public int $id, public int $assetId, public string $date, public Decimal $value,)
	{
	}

	public static function fromEntity(BenchmarkData $benchmarkData): self
	{
		return new self(
			id: $benchmarkData->getId(),
			assetId: $benchmarkData->getAsset()->getId(),
			date: DateTimeUtils::formatZulu($benchmarkData->getDate()),
			value: new Decimal($benchmarkData->getValue()),
		);
	}
}
