<?php

declare(strict_types=1);

namespace FinGather\Service\Dbal;

use Cycle\Database\DatabaseInterface;
use Cycle\Database\Query\DeleteQuery;
use Cycle\Database\Query\SelectQuery;
use Cycle\ORM\ORM;

/** @template TEntity of object */
final class QueryProvider
{
	private readonly DatabaseInterface $database;

	private readonly string $table;

	/** @param class-string<TEntity> $entityClass */
	public function __construct(string $entityClass, ORM $orm)
	{
		$source = $orm->getSource($entityClass);
		$this->database = $source->getDatabase();
		$this->table = $source->getTable();
	}

	/**
	 * @param array<string>|string $columns
	 * @return SelectQuery<TEntity>
	 */
	public function select(array|string $columns = '*'): SelectQuery
	{
		return $this->database->select($columns);
	}

	/** @api */
	public function delete(): DeleteQuery
	{
		return $this->database->delete($this->table);
	}

	/** @param callable(): void $callback */
	public function transaction(callable $callback): void
	{
		$this->database->transaction($callback);
	}
}
