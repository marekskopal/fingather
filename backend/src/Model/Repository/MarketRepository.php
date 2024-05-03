<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use Cycle\ORM\Select\Repository;
use FinGather\Model\Entity\Enum\MarketTypeEnum;
use FinGather\Model\Entity\Market;

/** @extends Repository<Market> */
final class MarketRepository extends Repository
{
	/** @var array<string,Market|null> */
	private array $marketsByExchangeCode = [];

	/** @return iterable<Market> */
	public function findMarkets(?MarketTypeEnum $type = null): iterable
	{
		if ($type === null) {
			return $this->findAll();
		}

		return $this->findAll([
			'type' => $type->value,
		]);
	}

	public function findMarketByMic(string $mic): ?Market
	{
		return $this->findOne([
			'mic' => $mic,
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
