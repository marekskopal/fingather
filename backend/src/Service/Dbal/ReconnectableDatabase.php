<?php

declare(strict_types=1);

namespace FinGather\Service\Dbal;

use MarekSkopal\ORM\Database\DatabaseInterface;
use MarekSkopal\ORM\Database\MySqlDatabase;
use PDO;

final class ReconnectableDatabase implements DatabaseInterface
{
	private MySqlDatabase $innerDatabase;

	private int $lastPingAt;

	/** Ping the connection if it has been idle for longer than this many seconds. */
	private const int PingThresholdSeconds = 3600;

	public function __construct(
		private readonly string $host,
		private readonly string $database,
		private readonly string $username,
		private readonly string $password,
	) {
		$this->innerDatabase = $this->createInnerDatabase();
		$this->lastPingAt = time();
	}

	public function getPdo(): PDO
	{
		return $this->getInnerDatabase()->getPdo();
	}

	public function getInnerDatabase(): MySqlDatabase
	{
		$this->pingIfIdle();
		return $this->innerDatabase;
	}

	private function pingIfIdle(): void
	{
		if (time() - $this->lastPingAt < self::PingThresholdSeconds) {
			return;
		}

		try {
			$this->innerDatabase->getPdo()->query('SELECT 1');
		} catch (\PDOException) {
			$this->innerDatabase = $this->createInnerDatabase();
		}

		$this->lastPingAt = time();
	}

	private function createInnerDatabase(): MySqlDatabase
	{
		return new MySqlDatabase($this->host, $this->username, $this->password, $this->database);
	}
}
