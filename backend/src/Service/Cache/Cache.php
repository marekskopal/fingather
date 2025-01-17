<?php

declare(strict_types=1);

namespace FinGather\Service\Cache;

use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use Nette\Caching\Storage;

final readonly class Cache
{
	private \Nette\Caching\Cache $cache;

	public function __construct(private Storage $storage, private string $namespace)
	{
		$this->cache = new \Nette\Caching\Cache($storage, $namespace);
	}

	public function save(
		string $key,
		mixed $data,
		?User $user = null,
		?Portfolio $portfolio = null,
		?DateTimeImmutable $date = null,
	): mixed {
		$dependencies = null;
		$tags = CacheTag::getForSave($this->namespace, $user, $portfolio, $date);
		if (count($tags) > 0) {
			$dependencies = [
				\Nette\Caching\Cache::Tags => $tags,
			];
		}

		return $this->cache->save($key, $data, $dependencies);
	}

	public function load(string $key): mixed
	{
		return $this->cache->load($key);
	}

	public function remove(string $key): void
	{
		$this->cache->remove($key);
	}

	public function clean(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$dependencies = null;
		$tags = CacheTag::getForClean($this->namespace, $user, $portfolio, $date);
		if (count($tags) > 0) {
			$dependencies = [
				\Nette\Caching\Cache::Tags => $tags,
			];
		}

		$this->cache->clean($dependencies);
	}

	public function cleanAll(): void
	{
		$this->cache->clean([\Nette\Caching\Cache::All => true]);
	}

	public function getStorage(): Storage
	{
		return $this->storage;
	}
}
