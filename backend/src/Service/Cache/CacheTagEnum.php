<?php

declare(strict_types=1);

namespace FinGather\Service\Cache;

enum CacheTagEnum: string
{
	case User = 'user';
	case Portfolio = 'portfolio';
	case Date = 'date';
}
