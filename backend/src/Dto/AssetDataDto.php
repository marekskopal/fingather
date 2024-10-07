<?php

declare(strict_types=1);

namespace FinGather\Dto;

use DateTimeImmutable;
use Decimal\Decimal;
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

	public static function fromEntity(\FinGather\Service\DataCalculator\Dto\AssetDataDto $assetData): self
	{
		return new self(
			date: DateTimeUtils::formatZulu($assetData->date),
			transactionValue: $assetData->transactionValue,
			transactionValueDefaultCurrency: $assetData->transactionValueDefaultCurrency,
			gain: $assetData->gain,
			gainDefaultCurrency: $assetData->gainDefaultCurrency,
		);
	}

	public static function fromNull(DateTimeImmutable $date): self
	{
		return new self(
			date: DateTimeUtils::formatZulu($date),
			transactionValue: new Decimal(0),
			transactionValueDefaultCurrency: new Decimal(0),
			gain: new Decimal(0),
			gainDefaultCurrency: new Decimal(0),
		);
	}
}
