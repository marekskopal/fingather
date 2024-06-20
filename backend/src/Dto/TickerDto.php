<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\TickerTypeEnum;
use FinGather\Model\Entity\Ticker;
use function Safe\json_decode;

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
			id: $ticker->getId(),
			ticker: $ticker->getTicker(),
			name: $ticker->getName(),
			marketId: $ticker->getMarket()->getId(),
			currencyId: $ticker->getCurrency()->getId(),
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

	/**
	 * @param array{
	 *     id: int,
	 *     ticker: string,
	 *     name: string,
	 *     marketId: int,
	 *     currencyId: int,
	 *     type: value-of<TickerTypeEnum>,
	 *     isin: string|null,
	 *     logo: string|null,
	 *     sector: array{
	 *         id: int,
	 *         name: string,
	 *     },
	 *     industry: array{
	 *         id: int,
	 *         name: string,
	 *     },
	 *     website: string|null,
	 *     description: string|null,
	 *     country: array{
	 *         id: int,
	 *         isoCode: string,
	 *         isoCode3: string,
	 *         name: string,
	 *     },
	 *     market: array{
	 *         name: string,
	 *         acronym: string,
	 *         mic: string,
	 *         exchangeCode: string,
	 *         country: string,
	 *         city: string,
	 *         timezone: string,
	 *         currencyId: int,
	 *     },
	 * } $data */
	private static function fromArray(array $data): self
	{
		return new self(
			id: $data['id'],
			ticker: $data['ticker'],
			name: $data['name'],
			marketId: $data['marketId'],
			currencyId: $data['currencyId'],
			type: TickerTypeEnum::from($data['type']),
			isin: $data['isin'],
			logo: $data['logo'],
			sector: SectorDto::fromArray($data['sector']),
			industry: IndustryDto::fromArray($data['industry']),
			website: $data['website'],
			description: $data['description'],
			country: CountryDto::fromArray($data['country']),
			market: MarketDto::fromArray($data['market']),
		);
	}

	public static function fromJson(string $json): self
	{
		/**
		 * @var array{
		 *     id: int,
		 *     ticker: string,
		 *     name: string,
		 *     marketId: int,
		 *     currencyId: int,
		 *     type: value-of<TickerTypeEnum>,
		 *     isin: string|null,
		 *     logo: string|null,
		 *     sector: array{
		 *         id: int,
		 *         name: string,
		 *     },
		 *     industry: array{
		 *         id: int,
		 *         name: string,
		 *     },
		 *     website: string|null,
		 *     description: string|null,
		 *     country: array{
		 *         id: int,
		 *         isoCode: string,
		 *         isoCode3: string,
		 *         name: string,
		 *     },
		 *     market: array{
		 *         name: string,
		 *         acronym: string,
		 *         mic: string,
		 *         exchangeCode: string,
		 *         country: string,
		 *         city: string,
		 *         timezone: string,
		 *         currencyId: int,
		 *     },
		 * } $data */
		$data = json_decode($json, assoc: true);
		return self::fromArray($data);
	}
}
