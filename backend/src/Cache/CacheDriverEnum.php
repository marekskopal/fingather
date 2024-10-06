<?php

namespace FinGather\Cache;

enum CacheDriverEnum: string
{
	case Memcached = 'memcached';
	case Redis = 'redis';
}
