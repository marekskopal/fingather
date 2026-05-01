<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Enum\TickerTypeEnum;
use FinGather\Model\Entity\ProxyAsset;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Repository\ProxyAssetRepository;
use Iterator;

final readonly class ProxyAssetProvider implements ProxyAssetProviderInterface
{
	public function __construct(private ProxyAssetRepository $proxyAssetRepository)
	{
	}

	/** @return Iterator<ProxyAsset> */
	public function getProxyAssets(): Iterator
	{
		return $this->proxyAssetRepository->findProxyAssets();
	}

	public function getProxyAsset(int $id): ?ProxyAsset
	{
		return $this->proxyAssetRepository->findProxyAssetById($id);
	}

	public function getProxyAssetByTickerType(TickerTypeEnum $tickerType): ?ProxyAsset
	{
		return $this->proxyAssetRepository->findProxyAssetByTickerType($tickerType);
	}

	public function createProxyAsset(TickerTypeEnum $tickerType, Ticker $ticker): ProxyAsset
	{
		$proxyAsset = new ProxyAsset(tickerType: $tickerType, ticker: $ticker);
		$this->proxyAssetRepository->persist($proxyAsset);

		return $proxyAsset;
	}

	public function deleteProxyAsset(ProxyAsset $proxyAsset): void
	{
		$this->proxyAssetRepository->delete($proxyAsset);
	}
}
