<?php

declare(strict_types=1);

namespace FinGather\Service\Cache;

use DateInterval;
use DateTimeImmutable;
use FinGather\Model\Entity\CacheTag;
use FinGather\Model\Repository\CacheTagRepository;

final class CacheWithTags extends Cache
{
	public function __construct(
		private readonly CacheTagRepository $cacheTagRepository,
		CacheDriverEnum $driver = CacheDriverEnum::Memcached,
		?string $namespace = null,
	)
	{
		parent::__construct($driver, $namespace);
	}

	public function setWithTags(
		string $key,
		mixed $value,
		DateInterval|int|null $ttl = null,
		?int $userId = null,
		?int $portfolioId = null,
		?DateTimeImmutable $date = null,
	): bool {
		$this->cacheTagRepository->getQueryProvider()->transaction(function () use ($key, $userId, $portfolioId, $date): void {
			$this->cacheTagRepository->getQueryProvider()->delete()
				->where('key', $this->namespace . $key)
				->run();
			$cacheTag = new CacheTag(
				key: $this->namespace . $key,
				driver: $this->driver,
				userId: $userId,
				portfolioId: $portfolioId,
				date: $date,
			);
			$this->cacheTagRepository->persist($cacheTag);
		});

		// TODO:Fix bulk insert and delete
		/*
		$this->cacheTagRepository->addToBulkDelete('key', $this->namespace . $key);
		$this->cacheTagRepository->addToBulkInsert(
			new CacheTag(
				key: $this->namespace . $key,
				driver: $this->driver,
				userId: $userId,
				portfolioId: $portfolioId,
				date: $date,
			),
		);
		*/
		return $this->set($key, $value, $ttl);
	}

	public function deleteWithTags(?int $userId = null, ?int $portfolioId = null, ?DateTimeImmutable $date = null,): void
	{
		if ($userId === null && $portfolioId === null && $date === null) {
			$this->clear();
		}

		$cacheTagKeys = $this->cacheTagRepository->findCacheTagKeys($this->driver, $userId, $portfolioId, $date);
		foreach ($cacheTagKeys as $key) {
			$this->storage->delete($key);
		}
		$this->cacheTagRepository->deleteCacheTag($this->driver, $userId, $portfolioId, $date);
	}
}
