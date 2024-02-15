<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Market;

final readonly class MarketDto
{
	public function __construct(
		public string $name,
		public string $acronym,
		public string $mic,
		public string $country,
		public string $city,
		public string $timezone,
		public int $currencyId,
	) {
	}

	public static function fromEntity(Market $market): self
	{
		return new self(
			name: $market->getName(),
			acronym: $market->getAcronym(),
			mic: $market->getMic(),
			country: $market->getCountry(),
			city: $market->getCity(),
			timezone: $market->getTimezone(),
			currencyId: $market->getCurrency()->getId(),
		);
	}

	/**
	 * @param array{
	 *     name: string,
	 *     acronym: string,
	 *     mic: string,
	 *     country: string,
	 *     city: string,
	 *     timezone: string,
	 *     currencyId: int,
	 * } $data */
	public static function fromArray(array $data): self
	{
		return new self(
			name: $data['name'],
			acronym: $data['acronym'],
			mic: $data['mic'],
			country: $data['country'],
			city: $data['city'],
			timezone: $data['timezone'],
			currencyId: $data['currencyId'],
		);
	}
}
