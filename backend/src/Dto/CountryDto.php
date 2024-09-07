<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Country;

final readonly class CountryDto
{
	public function __construct(public int $id, public string $isoCode, public string $isoCode3, public string $name,)
	{
	}

	public static function fromEntity(Country $country): self
	{
		return new self(
			id: $country->getId(),
			isoCode: $country->getIsoCode(),
			isoCode3: $country->getIsoCode3(),
			name: $country->getName(),
		);
	}
}
