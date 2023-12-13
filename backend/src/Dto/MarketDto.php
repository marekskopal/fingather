<?php

declare(strict_types=1);

namespace FinGather\Dto;

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
}
