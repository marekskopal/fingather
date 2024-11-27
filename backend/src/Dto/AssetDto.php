<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\Asset;

final readonly class AssetDto
{
	public function __construct(
		public int $id,
		public int $tickerId,
		public TickerDto $ticker,
		public ?int $groupId,
		public Decimal $price,
	) {
	}

	public static function fromEntity(Asset $asset, Decimal $price): self
	{
		return new self(
			id: $asset->id,
			tickerId: $asset->getTicker()->id,
			ticker: TickerDto::fromEntity($asset->getTicker()),
			groupId: $asset->getGroup()->id,
			price: $price,
		);
	}
}
