<?php

declare(strict_types=1);

namespace FinGather\Service\Cache;

use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use Nette\Caching\Cache;

enum CacheTagEnum: string
{
	case User = 'user';
	case Portfolio = 'portfolio';
	case Date = 'date';

	/** @return array{tags: array<string>} */
	public static function getCacheTags(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): array
	{
		$tags = [];
		if ($user !== null) {
			$tags[] = self::User->value . '-' . $user->getId();
		}
		if ($portfolio !== null) {
			$tags[] = self::Portfolio->value . '-' . $portfolio->getId();
		}
		if ($date !== null) {
			$tags[] = self::Date->value . '-' . $date->getTimestamp();
		}

		return [
			Cache::Tags => $tags,
		];
	}
}
