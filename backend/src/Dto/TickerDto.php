<?php

declare(strict_types=1);

namespace FinGather\Dto;

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
		public ?string $logo,
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
			logo: $ticker->getLogo(),
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
	 *     logo: string|null,
	 *     market: array{
	 *         name: string,
	 *         acronym: string,
	 *         mic: string,
	 *         country: string,
	 *         city: string,
	 *         timezone: string,
	 *         currencyId: int,
	 *     },
	 * } $data */
	public static function fromArray(array $data): self
	{
		return new self(
			id: $data['id'],
			ticker: $data['ticker'],
			name: $data['name'],
			marketId: $data['marketId'],
			currencyId: $data['currencyId'],
			logo: $data['logo'],
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
		 *     logo: string|null,
		 *     market: array{
		 *         name: string,
		 *         acronym: string,
		 *         mic: string,
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
