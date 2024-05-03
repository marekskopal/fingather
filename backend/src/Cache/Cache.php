<?php

declare(strict_types=1);

namespace FinGather\Cache;

use DateInterval;
use Psr\SimpleCache\CacheInterface;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\KeyValue\Factory;
use Spiral\RoadRunner\KeyValue\Serializer\IgbinarySerializer;
use Spiral\RoadRunner\KeyValue\StorageInterface;

final class Cache implements CacheInterface
{
	private readonly StorageInterface $storage;

	public function __construct(private readonly ?string $namespace = null)
	{
		/** @var non-empty-string $address */
		$address = Environment::fromGlobals()->getRPCAddress();
		$rpc = RPC::create($address);

		$this->storage = (new Factory($rpc))
			->withSerializer(new IgbinarySerializer())
			->select('memcached');
	}

	public function get(string $key, mixed $default = null): mixed
	{
		return $this->storage->get($this->namespace . $key);
	}

	public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool
	{
		return $this->storage->set($this->namespace . $key, $value, $ttl);
	}

	public function delete(string $key): bool
	{
		return $this->storage->delete($this->namespace . $key);
	}

	public function clear(): bool
	{
		return $this->storage->clear();
	}

	/**
	 * @param iterable<string> $keys
	 * @return iterable<string, mixed>
	 */
	public function getMultiple(iterable $keys, mixed $default = null): iterable
	{
		return $this->storage->getMultiple($keys, $default);
	}

	/** @param iterable<mixed> $values */
	public function setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool
	{
		return $this->storage->setMultiple($values, $ttl);
	}

	/** @param iterable<string> $keys */
	public function deleteMultiple(iterable $keys): bool
	{
		return $this->storage->deleteMultiple($keys);
	}

	public function has(string $key): bool
	{
		return $this->storage->has($this->namespace . $key);
	}
}
