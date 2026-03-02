<?php

declare(strict_types=1);

namespace FinGather\Service\Dbal;

use MarekSkopal\ORM\Database\DatabaseInterface;
use MarekSkopal\ORM\Database\MySqlDatabase;
use PDO;

final class ReconnectableDatabase implements DatabaseInterface
{
	private PDO $pdo;

	private int $lastPingAt;

	/** Ping the connection if it has been idle for longer than this many seconds. */
	private const int PingThresholdSeconds = 3600;

	public function __construct(
		private readonly string $host,
		private readonly string $database,
		private readonly string $username,
		private readonly string $password,
	) {
		$this->pdo = $this->createPdo();
		$this->lastPingAt = time();
	}

	public function getPdo(): PDO
	{
		$this->pingIfIdle();
		return $this->pdo;
	}

	private function pingIfIdle(): void
	{
		if (time() - $this->lastPingAt < self::PingThresholdSeconds) {
			return;
		}

		try {
			$this->pdo->query('SELECT 1');
		} catch (\PDOException) {
			$this->pdo = $this->createPdo();
		}

		$this->lastPingAt = time();
	}

	private function createPdo(): PDO
	{
		return new MySqlDatabase($this->host, $this->username, $this->password, $this->database)->getPdo();
	}
}
