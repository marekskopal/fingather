<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\TickerTypeEnum;
use FinGather\Model\Entity\ProxyAsset;

final readonly class ProxyAssetDto
{
	public function __construct(public int $id, public TickerTypeEnum $tickerType, public TickerDto $ticker,)
	{
	}

	public static function fromEntity(ProxyAsset $proxyAsset): self
	{
		return new self(
			id: $proxyAsset->id,
			tickerType: $proxyAsset->tickerType,
			ticker: TickerDto::fromEntity($proxyAsset->ticker),
		);
	}
}
