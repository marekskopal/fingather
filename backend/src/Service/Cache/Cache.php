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
		?int $expireSeconds = null,
	): mixed {
		$dependencies = null;
		$tags = CacheTag::getForSave($this->namespace, $user, $portfolio, $date);
		if (count($tags) > 0) {
			$dependencies = [
				\Nette\Caching\Cache::Tags => $tags,
			];
		}

		if ($expireSeconds !== null) {
			$dependencies[\Nette\Caching\Cache::Expire] = $expireSeconds;
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
		$tags = CacheTag::getForClean($this->namespace, $user, $portfolio, $date);
		if (count($tags) === 0) {
			// nette/caching 4 throws on clean() without conditions; keep the storage-level cleanup of expired items
			$this->storage->clean([]);

			return;
		}

		$this->cache->clean(tags: $tags);
	}

	public function cleanAll(): void
	{
		// Go to the storage directly: since nette/caching 4, Cache::clean(all: true) on a namespaced cache only cleans
		// the namespace, which neither MemcachedStorage nor contributte's RedisStorage supports. Flush everything as before.
		$this->storage->clean([\Nette\Caching\Cache::All => true]);
	}

	public function getStorage(): Storage
	{
		return $this->storage;
	}
}
