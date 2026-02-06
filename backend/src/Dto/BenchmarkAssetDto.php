<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\BenchmarkAsset;

final readonly class BenchmarkAssetDto
{
	public function __construct(public int $id, public TickerDto $ticker,)
	{
	}

	public static function fromEntity(BenchmarkAsset $benchmarkAsset): self
	{
		return new self(
			id: $benchmarkAsset->id,
			ticker: TickerDto::fromEntity($benchmarkAsset->ticker),
		);
	}
}
