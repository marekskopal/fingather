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
		$namespaceKey = $namespace ?? '';

		if (isset($this->caches[$driver->value][$namespaceKey])) {
			return $this->caches[$driver->value][$namespaceKey];
		}

		$this->caches[$driver->value][$namespaceKey] = new CacheWithTags($this->cacheTagRepository, driver: $driver, namespace: $namespace);

		return $this->caches[$driver->value][$namespaceKey];
	}
}
