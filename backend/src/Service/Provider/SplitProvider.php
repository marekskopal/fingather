<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Split;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Repository\SplitRepository;
use FinGather\Service\Cache\CacheFactory;
use FinGather\Service\Provider\Dto\SplitDto;
use Nette\Caching\Cache;

class SplitProvider
{
	private readonly Cache $cache;

	public function __construct(private readonly SplitRepository $splitRepository, CacheFactory $cacheFactory,)
	{
		$this->cache = $cacheFactory->create(namespace: self::class);
	}

	/** @return list<SplitDto> */
	public function getSplits(Ticker $ticker): array
	{
		$key = (string) $ticker->getId();

		/** @var list<SplitDto>|null $splits */
		$splits = $this->cache->load($key);
		if ($splits !== null) {
			return $splits;
		}

		$splits = array_map(
			fn(Split $split): SplitDto => SplitDto::fromEntity($split),
			$this->splitRepository->findSplits($ticker->getId()),
		);
		$this->cache->save($key, $splits);

		return $splits;
	}

	public function getSplit(Ticker $ticker, ?DateTimeImmutable $date = null): ?Split
	{
		return $this->splitRepository->findSplit($ticker->getId(), $date);
	}

	public function createSplit(Ticker $ticker, DateTimeImmutable $date, Decimal $factor): Split
	{
		$split = new Split(tickerId: $ticker->getId(), date: $date, factor: $factor);
		$this->splitRepository->persist($split);

		return $split;
	}

	public function cleanCache(Ticker $ticker): void
	{
		$this->cache->remove((string) $ticker->getId());
	}
}
