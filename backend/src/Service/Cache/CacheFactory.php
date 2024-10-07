<?php

declare(strict_types=1);

namespace FinGather\Service\Cache;

use FinGather\Model\Repository\CacheTagRepository;

final class CacheFactory
{
	/** @var array<string, array<string, CacheWithTags>> */
	private array $caches = [];

	public function __construct(private readonly CacheTagRepository $cacheTagRepository)
	{
	}

	public function create(CacheDriverEnum $driver = CacheDriverEnum::Memcached, ?string $namespace = null): CacheWithTags
	{
		if (isset($this->caches[$driver->value][(string) $namespace])) {
			return $this->caches[$driver->value][(string) $namespace];
		}

		$this->caches[$driver->value][(string) $namespace] = new CacheWithTags(
			$this->cacheTagRepository,
			driver: $driver,
			namespace: $namespace,
		);

		return $this->caches[$driver->value][(string) $namespace];
	}
}
