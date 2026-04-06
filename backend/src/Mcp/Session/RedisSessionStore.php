<?php

declare(strict_types=1);

namespace FinGather\Mcp\Session;

use Mcp\Server\Session\SessionStoreInterface;
use Predis\ClientInterface;
use Symfony\Component\Uid\Uuid;

final readonly class RedisSessionStore implements SessionStoreInterface
{
	private const string KeyPrefix = 'mcp:session:';

	public function __construct(private ClientInterface $redis, private int $ttl = 3600,)
	{
	}

	public function exists(Uuid $id): bool
	{
		return (bool) $this->redis->exists($this->key($id));
	}

	public function read(Uuid $id): string|false
	{
		$value = $this->redis->get($this->key($id));

		return $value ?? false;
	}

	public function write(Uuid $id, string $data): bool
	{
		$this->redis->setex($this->key($id), $this->ttl, $data);

		return true;
	}

	public function destroy(Uuid $id): bool
	{
		$this->redis->del([$this->key($id)]);

		return true;
	}

	/** @return Uuid[] */
	public function gc(): array
	{
		// Redis handles TTL-based expiry automatically — no manual GC needed.
		return [];
	}

	private function key(Uuid $id): string
	{
		return self::KeyPrefix . $id->toRfc4122();
	}
}
