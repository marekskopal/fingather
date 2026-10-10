<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Enum\TickerTypeEnum;
use FinGather\Model\Entity\ProxyAsset;
use FinGather\Model\Entity\Ticker;

interface ProxyAssetProviderInterface
{
	/** @return list<ProxyAsset> */
	public function getProxyAssets(): array;

	public function getProxyAsset(int $id): ?ProxyAsset;

	public function getProxyAssetByTickerType(TickerTypeEnum $tickerType): ?ProxyAsset;

	public function createProxyAsset(TickerTypeEnum $tickerType, Ticker $ticker): ProxyAsset;

	public function deleteProxyAsset(ProxyAsset $proxyAsset): void;
}
