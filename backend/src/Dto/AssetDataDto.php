<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\AssetData;
use FinGather\Utils\DateTimeUtils;

final readonly class AssetDataDto
{
	public function __construct(
		public string $date,
		public Decimal $transactionValue,
		public Decimal $transactionValueDefaultCurrency,
		public Decimal $gain,
		public Decimal $gainDefaultCurrency,
	) {
	}

	public static function fromEntity(AssetData $assetData): self
	{
		return new self(
			date: DateTimeUtils::formatZulu($assetData->getDate()),
			transactionValue: $assetData->getTransactionValue(),
			transactionValueDefaultCurrency: $assetData->getTransactionValueDefaultCurrency(),
			gain: $assetData->getGain(),
			gainDefaultCurrency: $assetData->getGainDefaultCurrency(),
		);
	}
}
