<?php

declare(strict_types=1);

namespace FinGather\Service\Cache;

enum CacheStorageEnum: string
{
	case Memcached = 'memcached';
	case Redis = 'redis';
}
