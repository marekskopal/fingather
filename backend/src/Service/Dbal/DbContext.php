<?php

declare(strict_types=1);

namespace FinGather\Service\Dbal;

use Cycle\Annotated\Embeddings;
use Cycle\Annotated\Entities;
use Cycle\Annotated\Locator\TokenizerEmbeddingLocator;
use Cycle\Annotated\Locator\TokenizerEntityLocator;
use Cycle\Annotated\MergeColumns;
use Cycle\Annotated\MergeIndexes;
use Cycle\Annotated\TableInheritance;
use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\Config\MySQL\DsnConnectionConfig;
use Cycle\Database\Config\MySQLDriverConfig;
use Cycle\Database\DatabaseManager;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\FileRepository;
use Cycle\Migrations\Migrator;
use Cycle\ORM\Factory;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Schema;
use Cycle\Schema\Compiler;
use Cycle\Schema\Generator\ForeignKeys;
use Cycle\Schema\Generator\GenerateModifiers;
use Cycle\Schema\Generator\GenerateRelations;
use Cycle\Schema\Generator\GenerateTypecast;
use Cycle\Schema\Generator\RenderModifiers;
use Cycle\Schema\Generator\RenderRelations;
use Cycle\Schema\Generator\RenderTables;
use Cycle\Schema\Generator\ResetTables;
use Cycle\Schema\Generator\ValidateEntities;
use Cycle\Schema\Registry;
use FinGather\Cache\Cache;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Tokenizer;

final readonly class DbContext
{
	private const string CacheNamespace = 'Orm';
	private const string CacheKey = 'Schema';

	private ORMInterface $orm;

	private DatabaseProviderInterface $dbal;

	private Registry $registry;

	/**
	 * @param non-empty-string $dsn
	 * @param non-empty-string $user
	 * @param non-empty-string $password
	 */
	public function __construct(string $dsn, string $user, string $password)
	{
		$this->dbal = new DatabaseManager(
			new DatabaseConfig([
				'default' => 'default',
				'databases' => [
					'default' => [
						'connection' => 'mariadb',
					],
				],
				'connections' => [
					'mariadb' => new MySQLDriverConfig(
						connection: new DsnConnectionConfig(
							dsn: $dsn,
							user: $user,
							password: $password,
						),
					),
				],
			]),
		);

		$this->registry = new Registry($this->dbal);

		// Initialize annotations
		//AnnotationRegistry::registerLoader('class_exists');

		$cache = new Cache(namespace: self::CacheNamespace);
		$schema = $cache->get(self::CacheKey);
		if ($schema !== null && is_array($schema)) {
			$this->orm = new ORM(new Factory($this->dbal), new Schema($schema));
			return;
		}

		$classLocator = (new Tokenizer(new TokenizerConfig([
			'directories' => [
				__DIR__ . '/../../Model/Entity',
			],
		])))->classLocator();

		$schema = (new Compiler())->compile($this->registry, [
			// Reconfigure table schemas (deletes columns if necessary)
			new ResetTables(),
			// Recognize embeddable entities
			new Embeddings(new TokenizerEmbeddingLocator($classLocator)),
			// Identify attributed entities
			new Entities(new TokenizerEntityLocator($classLocator)),
			// Setup Single Table or Joined Table Inheritance
			new TableInheritance(),
			// Integrate table #[Column] attributes
			new MergeColumns(),
			// Define entity relationships
			new GenerateRelations(),
			// Apply schema modifications
			new GenerateModifiers(),
			// Ensure entity schemas adhere to conventions
			new ValidateEntities(),
			// Create table schemas
			new RenderTables(),
			// Establish keys and indexes for relationships
			new RenderRelations(),
			// Implement schema modifications
			new RenderModifiers(),
			// Define foreign key constraints
			new ForeignKeys(),
			// Merge table index attributes
			new MergeIndexes(),
			// Typecast non-string columns
			new GenerateTypecast(),
		]);

		$cache->set(self::CacheKey, $schema);

		$this->orm = new ORM(new Factory($this->dbal), new Schema($schema));
	}

	public function getOrm(): ORMInterface
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

	public function getRegistry(): Registry
	{
		return $this->registry;
	}
}
