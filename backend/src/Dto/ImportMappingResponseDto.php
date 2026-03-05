<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\ImportMapping;

final readonly class ImportMappingResponseDto
{
	public function __construct(
		public int $id,
		public int $brokerId,
		public string $brokerName,
		public string $importTicker,
		public int $tickerId,
		public string $tickerName,
		public string $tickerTicker,
		public string $tickerMarketMic,
		public int $tickerCurrencyId,
	) {
	}

	public static function fromEntity(ImportMapping $importMapping): self
	{
		return new self(
			id: $importMapping->id,
			brokerId: $importMapping->broker->id,
			brokerName: $importMapping->broker->name,
			importTicker: $importMapping->importTicker,
			tickerId: $importMapping->ticker->id,
			tickerName: $importMapping->ticker->name,
			tickerTicker: $importMapping->ticker->ticker,
			tickerMarketMic: $importMapping->ticker->market->mic,
			tickerCurrencyId: $importMapping->ticker->currency->id,
		);
	}
}
