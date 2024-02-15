<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Market;
use FinGather\Model\Repository\MarketRepository;

class MarketProvider
{
	public function __construct(private readonly MarketRepository $marketRepository)
	{
	}

	public function getMarketByMic(string $mic): ?Market
	{
		return $this->marketRepository->findMarketByMic($mic);
	}
}
