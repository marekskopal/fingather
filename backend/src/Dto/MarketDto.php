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
		public string $exchangeCode,
		public string $country,
		public string $city,
		public string $timezone,
		public int $currencyId,
	) {
	}

	public static function fromEntity(Market $market): self
	{
		return new self(
			name: $market->name,
			acronym: $market->acronym,
			mic: $market->mic,
			exchangeCode: $market->exchangeCode,
			country: $market->country,
			city: $market->city,
			timezone: $market->timezone,
			currencyId: $market->currency->id,
		);
	}
}
