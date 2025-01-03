<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Enum\MarketTypeEnum;
use FinGather\Model\Entity\Market;
use Iterator;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<Market> */
final class MarketRepository extends AbstractRepository
{
	/** @var array<string,Market|null> */
	private array $marketsByExchangeCode = [];

	/** @return Iterator<Market> */
	public function findMarkets(?MarketTypeEnum $type = null): Iterator
	{
		if ($type === null) {
			return $this->findAll();
		}

		return $this->findAll([
			'type' => $type->value,
		]);
	}

	public function findMarketByType(MarketTypeEnum $type): ?Market
	{
		return $this->findOne([
			'type' => $type->value,
		]);
	}

	public function findMarketByExchangeCode(string $exchangeCode): ?Market
	{
		if (isset($this->marketsByExchangeCode[$exchangeCode])) {
			return $this->marketsByExchangeCode[$exchangeCode];
		}

		$this->marketsByExchangeCode[$exchangeCode] = $this->findOne([
			'exchange_code' => $exchangeCode,
		]);

		return $this->marketsByExchangeCode[$exchangeCode];
	}
}
