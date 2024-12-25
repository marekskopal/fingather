<?php

declare(strict_types=1);

namespace FinGather\Service\Dbal;

use FinGather\Service\Cache\CacheFactory;
use MarekSkopal\ORM\Database\MySqlDatabase;
use MarekSkopal\ORM\Migrations\Migrator;
use MarekSkopal\ORM\ORM;
use MarekSkopal\ORM\Schema\Builder\SchemaBuilder;
use MarekSkopal\ORM\Schema\Schema;

final readonly class DbContext
{
	private const string CacheNamespace = 'Orm';
	private const string CacheKey = 'Schema';

	private MySqlDatabase $database;

	private Schema $schema;

	private ORM $orm;

	public function __construct(string $host, string $name, string $user, string $password)
	{
		$this->database = new MySqlDatabase($host, $user, $password, $name);

		$cache = CacheFactory::createPsrCache(namespace: self::CacheNamespace);
		$schema = $cache->get(self::CacheKey);
		if ($schema instanceof Schema) {
			$this->schema = $schema;
			$this->orm = new ORM($this->database, $schema);
			return;
		}

		$this->schema = new SchemaBuilder()
			->addEntityPath(__DIR__ . '/../../Model/Entity')
			->build();

		$cache->set(self::CacheKey, $this->schema);

		$this->orm = new ORM($this->database, $this->schema);
	}

	public function getOrm(): ORM
	{
		return $this->orm;
	}

	public function getMigrator(): Migrator
	{
		return new Migrator(__DIR__ . '/../../../migrations/', $this->database);
	}

	public function getSchema(): Schema
	{
		return $this->schema;
	}
}
