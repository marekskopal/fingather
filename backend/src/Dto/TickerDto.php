<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\TickerTypeEnum;
use FinGather\Model\Entity\Ticker;

final readonly class TickerDto
{
	public function __construct(
		public int $id,
		public string $ticker,
		public string $name,
		public int $marketId,
		public int $currencyId,
		public TickerTypeEnum $type,
		public ?string $isin,
		public ?string $logo,
		public SectorDto $sector,
		public IndustryDto $industry,
		public ?string $website,
		public ?string $description,
		public CountryDto $country,
		public MarketDto $market,
	) {
	}

	public static function fromEntity(Ticker $ticker): self
	{
		return new self(
			id: $ticker->id,
			ticker: $ticker->ticker,
			name: $ticker->name,
			marketId: $ticker->market->id,
			currencyId: $ticker->currency->id,
			type: $ticker->type,
			isin: $ticker->isin,
			logo: $ticker->logo,
			sector: SectorDto::fromEntity($ticker->sector),
			industry: IndustryDto::fromEntity($ticker->industry),
			website: $ticker->website,
			description: $ticker->description,
			country: CountryDto::fromEntity($ticker->country),
			market: MarketDto::fromEntity($ticker->market),
		);
	}
}
