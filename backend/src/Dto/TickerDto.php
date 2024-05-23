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
		public ?TickerSectorDto $sector,
		public ?TickerIndustryDto $industry,
		public ?string $website,
		public ?string $description,
		public ?CountryDto $country,
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
			sector: $ticker->getSector() !== null ? TickerSectorDto::fromEntity($ticker->getSector()) : null,
			industry: $ticker->getIndustry() !== null ? TickerIndustryDto::fromEntity($ticker->getIndustry()) : null,
			website: $ticker->getWebsite(),
			description: $ticker->getDescription(),
			country: $ticker->getCountry() !== null ? CountryDto::fromEntity($ticker->getCountry()) : null,
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
	 *     }|null,
	 *     industry: array{
	 *         id: int,
	 *         name: string,
	 *     }|null,
	 *     website: string|null,
	 *     description: string|null,
	 *     country: array{
	 *         id: int,
	 *         iso_code: string,
	 *         iso_code3: string,
	 *         name: string,
	 *     }|null,
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
			sector: ($data['sector'] ?? null) !== null ? TickerSectorDto::fromArray($data['sector']) : null,
			industry: ($data['industry'] ?? null) !== null ? TickerIndustryDto::fromArray($data['industry']) : null,
			website: $data['website'],
			description: $data['description'],
			country: ($data['country'] ?? null) !== null ? CountryDto::fromArray($data['country']) : null,
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
		 *     }|null,
		 *     industry: array{
		 *         id: int,
		 *         name: string,
		 *     }|null,
		 *     website: string|null,
		 *     description: string|null,
		 *     country: array{
		 *         id: int,
		 *         iso_code: string,
		 *         iso_code3: string,
		 *         name: string,
		 *     }|null,
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
