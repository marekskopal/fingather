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
		public string $web,
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
			web: $market->getWeb(),
			currencyId: $market->getCurrency()->getId(),
		);
	}
}
