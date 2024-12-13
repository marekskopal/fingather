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
			ticker: $ticker->getTicker(),
			name: $ticker->getName(),
			marketId: $ticker->getMarket()->id,
			currencyId: $ticker->getCurrency()->id,
			type: $ticker->getType(),
			isin: $ticker->getIsin(),
			logo: $ticker->getLogo(),
			sector: SectorDto::fromEntity($ticker->getSector()),
			industry: IndustryDto::fromEntity($ticker->getIndustry()),
			website: $ticker->getWebsite(),
			description: $ticker->getDescription(),
			country: CountryDto::fromEntity($ticker->getCountry()),
			market: MarketDto::fromEntity($ticker->getMarket()),
		);
	}
}
