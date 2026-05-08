<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfValuationStatusEnum;
use FinGather\Service\Provider\Dto\DcfValuationChipDto;

final readonly class AssetDto
{
	public function __construct(
		public int $id,
		public int $tickerId,
		public TickerDto $ticker,
		public ?int $groupId,
		public Decimal $price,
		public ?float $dcfValuationDiffPercent,
		public ?DcfValuationStatusEnum $dcfValuationStatus,
	) {
	}

	public static function fromEntity(Asset $asset, Decimal $price, ?DcfValuationChipDto $dcfValuationChip,): self
	{
		return new self(
			id: $asset->id,
			tickerId: $asset->ticker->id,
			ticker: TickerDto::fromEntity($asset->ticker),
			groupId: $asset->group->id,
			price: $price,
			dcfValuationDiffPercent: $dcfValuationChip?->diffPercent,
			dcfValuationStatus: $dcfValuationChip?->status,
		);
	}
}
