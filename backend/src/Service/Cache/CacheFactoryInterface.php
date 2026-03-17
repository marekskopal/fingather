<?php

declare(strict_types=1);

namespace FinGather\Service\Cache;

interface CacheFactoryInterface
{
	public function create(CacheStorageEnum $driver = CacheStorageEnum::Memcached, ?string $namespace = null): Cache;
}
