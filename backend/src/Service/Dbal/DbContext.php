<?php

declare(strict_types=1);

namespace FinGather\Service\Dbal;

use FinGather\Service\Cache\CacheFactory;
use MarekSkopal\ORM\Database\MySqlDatabase;
use MarekSkopal\ORM\ORM;
use MarekSkopal\ORM\Schema\Builder\SchemaBuilder;
use MarekSkopal\ORM\Schema\Schema;

final readonly class DbContext
{
	private const string CacheNamespace = 'Orm';
	private const string CacheKey = 'Schema';

	private ORM $orm;

	public function __construct(string $host, string $database, string $user, string $password)
	{
		$database = new MySqlDatabase($host, $user, $password, $database);

		$cache = CacheFactory::createPsrCache(namespace: self::CacheNamespace);
		$schema = $cache->get(self::CacheKey);
		if ($schema instanceof Schema) {
			$this->orm = new ORM($database, $schema);
			return;
		}

		$schema = new SchemaBuilder()
			->addEntityPath(__DIR__ . '/../../Model/Entity')
			->build();

		$cache->set(self::CacheKey, $schema);

		$this->orm = new ORM($database, $schema);
	}

	public function getOrm(): ORM
	{
		return $this->orm;
	}

	public function getMigrator(): Migrator
	{
		$migrationConfig = new MigrationConfig([
			// where to store migrations
			'directory' => __DIR__ . '/../../../migrations/',
			// database table to store migration status
			'table' => 'migrations',
		]);

		$migrator = new Migrator($migrationConfig, $this->dbal, new FileRepository($migrationConfig));

		$migrator->configure();

		return $migrator;
	}
}
