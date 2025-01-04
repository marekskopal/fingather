<?php

declare(strict_types=1);

namespace FinGather\Service\Cache;

use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

final class CacheTag
{
	/** @return list<string> */
	public static function getForSave(
		string $namespace,
		?User $user = null,
		?Portfolio $portfolio = null,
		?DateTimeImmutable $date = null,
	): array
	{
		if ($user === null && $portfolio === null && $date === null) {
			return [];
		}

		return array_values(array_unique(array_merge(
			self::getCacheTagsOr($namespace, $user, $portfolio, $date),
			self::getCacheTagsAnd($namespace, $user, $portfolio, $date),
		)));
	}

	/** @return list<string> */
	public static function getForClean(
		string $namespace,
		?User $user = null,
		?Portfolio $portfolio = null,
		?DateTimeImmutable $date = null,
	): array
	{
		if ($user === null && $portfolio === null && $date === null) {
			return [];
		}

		return self::getCacheTagsAnd($namespace, $user, $portfolio, $date);
	}

	/** @return list<string> */
	private static function getCacheTagsOr(
		string $namespace,
		?User $user = null,
		?Portfolio $portfolio = null,
		?DateTimeImmutable $date = null,
	): array
	{
		$tags = [];
		if ($user !== null) {
			$tags[] = $namespace . '-' . CacheTagEnum::User->value . '-' . $user->id;
		}
		if ($portfolio !== null) {
			$tags[] = $namespace . '-' . CacheTagEnum::Portfolio->value . '-' . $portfolio->id;
		}
		if ($date !== null) {
			$tags[] = $namespace . '-' . CacheTagEnum::Date->value . '-' . $date->getTimestamp();
		}

		return $tags;
	}

	/** @return array{0: string} */
	private static function getCacheTagsAnd(
		string $namespace,
		?User $user = null,
		?Portfolio $portfolio = null,
		?DateTimeImmutable $date = null,
	): array
	{
		$tags = [];
		if ($user !== null) {
			$tags[] = CacheTagEnum::User->value . '-' . $user->id;
		}
		if ($portfolio !== null) {
			$tags[] = CacheTagEnum::Portfolio->value . '-' . $portfolio->id;
		}
		if ($date !== null) {
			$tags[] = CacheTagEnum::Date->value . '-' . $date->getTimestamp();
		}

		return [
			$namespace . '-' . implode('|', $tags),
		];
	}
}
