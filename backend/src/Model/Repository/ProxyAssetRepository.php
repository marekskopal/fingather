<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Enum\TickerTypeEnum;
use FinGather\Model\Entity\ProxyAsset;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<ProxyAsset> */
final class ProxyAssetRepository extends AbstractRepository
{
	/** @return list<ProxyAsset> */
	public function findProxyAssets(): array
	{
		return $this->findAll();
	}

	public function findProxyAssetById(int $id): ?ProxyAsset
	{
		return $this->findOne(['id' => $id]);
	}

	public function findProxyAssetByTickerType(TickerTypeEnum $tickerType): ?ProxyAsset
	{
		return $this->findOne(['ticker_type' => $tickerType->value]);
	}
}
