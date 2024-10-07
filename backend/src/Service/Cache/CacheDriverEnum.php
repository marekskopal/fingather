<?php

declare(strict_types=1);

namespace FinGather\Service\Cache;

enum CacheDriverEnum: string
{
	case Memcached = 'memcached';
	case Redis = 'redis';
}
