<?php

declare(strict_types=1);

namespace FinGather\Service\Dbal;

use MarekSkopal\ORM\Database\DatabaseInterface;
use MarekSkopal\ORM\Database\MySqlDatabase;
use PDO;
use PDOException;
use PDOStatement;

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

	public function connect(): void
	{
		$this->getInnerDatabase()->connect();
	}

	public function isConnected(): bool
	{
		return $this->innerDatabase->isConnected();
	}

	/** @param list<mixed> $params */
	public function execute(string $sql, array $params = [], bool $cached = true): PDOStatement
	{
		return $this->getInnerDatabase()->execute($sql, $params, $cached);
	}

	public function prepareCached(string $sql): PDOStatement
	{
		return $this->getInnerDatabase()->prepareCached($sql);
	}

	public function clearStatementCache(): void
	{
		$this->innerDatabase->clearStatementCache();
	}

	public function getIdentifierQuoteChar(): string
	{
		return '`';
	}

	public function getInsertReturningClause(string $primaryColumnName): string
	{
		return '';
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

		// The connection opens lazily on the first query; never open one just to ping it.
		if (!$this->innerDatabase->isConnected()) {
			$this->lastPingAt = time();
			return;
		}

		try {
			$this->innerDatabase->getPdo()->query('SELECT 1');
		} catch (PDOException) {
			$this->innerDatabase = $this->createInnerDatabase();
		}

		$this->lastPingAt = time();
	}

	private function createInnerDatabase(): MySqlDatabase
	{
		return new MySqlDatabase($this->host, $this->username, $this->password, $this->database);
	}
}
