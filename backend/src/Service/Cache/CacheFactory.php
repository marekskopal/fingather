<?php

declare(strict_types=1);

namespace FinGather\Service\Cache;

use Contributte\Redis\Caching\RedisJournal;
use Contributte\Redis\Caching\RedisStorage;
use Contributte\Redis\Serializer\IgbinarySerializer;
use Nette\Bridges\Psr\PsrCacheAdapter;
use Nette\Caching\Storages\MemcachedStorage;
use Predis\ClientInterface;
use Psr\SimpleCache\CacheInterface;

final class CacheFactory
{
	/** @var array<string, array<string, Cache>> */
	private array $caches = [];

	public function __construct(private readonly ClientInterface $clientInterface)
	{
	}

	public function create(CacheStorageEnum $driver = CacheStorageEnum::Memcached, ?string $namespace = null): Cache
	{
		$namespaceKey = $namespace ?? '';

		if (isset($this->caches[$driver->value][$namespaceKey])) {
			return $this->caches[$driver->value][$namespaceKey];
		}

		$this->caches[$driver->value][$namespaceKey] = match ($driver) {
			CacheStorageEnum::Memcached => self::createMemcachedCache($namespaceKey),
			CacheStorageEnum::Redis => self::createRedisCache($this->clientInterface, $namespaceKey),
		};

		return $this->caches[$driver->value][$namespaceKey];
	}

	public static function createPsrCache(CacheStorageEnum $driver = CacheStorageEnum::Memcached, ?string $namespace = null): CacheInterface
	{
		$namespaceKey = $namespace ?? '';

		$cache = match ($driver) {
			CacheStorageEnum::Memcached => self::createMemcachedCache($namespaceKey),
			CacheStorageEnum::Redis => throw new \RuntimeException('Not implemented yet'),
		};
		return new PsrCacheAdapter($cache->getStorage());
	}

	private static function createMemcachedCache(string $namespace): Cache
	{
		$storage = new MemcachedStorage(
			host: (string) getenv('MEMCACHED_HOST'),
			port: (int) getenv('MEMCACHED_PORT'),
		);
		return new Cache($storage, $namespace);
	}

	private static function createRedisCache(ClientInterface $clientInterface, string $namespace): Cache
	{
		$storage = new RedisStorage($clientInterface, new RedisJournal($clientInterface), new IgbinarySerializer());
		return new Cache($storage, $namespace);
	}
}
