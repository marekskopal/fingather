<?php

declare(strict_types=1);

namespace FinGather\Service\Dbal;

use Cycle\Annotated\Embeddings;
use Cycle\Annotated\Entities;
use Cycle\Annotated\MergeColumns;
use Cycle\Annotated\MergeIndexes;
use Cycle\Annotated\TableInheritance;
use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\Config\MySQL\DsnConnectionConfig;
use Cycle\Database\Config\MySQLDriverConfig;
use Cycle\Database\DatabaseManager;
use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\FileRepository;
use Cycle\Migrations\Migrator;
use Cycle\ORM\Factory;
use Cycle\ORM\ORM;
use Cycle\ORM\Schema;
use Cycle\Schema\Compiler;
use Cycle\Schema\Generator\ForeignKeys;
use Cycle\Schema\Generator\GenerateModifiers;
use Cycle\Schema\Generator\GenerateRelations;
use Cycle\Schema\Generator\GenerateTypecast;
use Cycle\Schema\Generator\Migrations\GenerateMigrations;
use Cycle\Schema\Generator\RenderModifiers;
use Cycle\Schema\Generator\RenderRelations;
use Cycle\Schema\Generator\RenderTables;
use Cycle\Schema\Generator\ResetTables;
use Cycle\Schema\Generator\SyncTables;
use Cycle\Schema\Generator\ValidateEntities;
use Cycle\Schema\Registry;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\Finder\Finder;

class DbContext
{
	private readonly ORM $orm;

	private readonly Migrator $migrator;

	private readonly Registry $registry;

	public function __construct(
		string $dsn,
		string $user,
		string $password,
	) {
		$dbal = new DatabaseManager(
			new DatabaseConfig([
				'default' => 'default',
				'databases' => [
					'default' => [
						'connection' => 'mariadb'
					]
				],
				'connections' => [
					'mariadb' => new MySQLDriverConfig(
						connection: new DsnConnectionConfig(
							dsn: $dsn,
							user: $user,
							password: $password,
						)
					),
				]
			])
		);

		$finder = (new Finder())->files()->in([
			__DIR__ . '/../../Model/Entity',
		]);
		$classLocator = new ClassLocator($finder);

		$migrationConfig = new MigrationConfig([
			'directory' => __DIR__ . '/../../../migrations/',  // where to store migrations
			'table' => 'migrations'                      // database table to store migration status
		]);

		$this->migrator = new Migrator($migrationConfig, $dbal, new FileRepository($migrationConfig));

		$this->migrator->configure();

		// Initialize annotations
		//AnnotationRegistry::registerLoader('class_exists');

		$this->registry = new Registry($dbal);

		$schema = (new Compiler())->compile($this->registry, [
			new ResetTables(),             // Reconfigure table schemas (deletes columns if necessary)
			new Embeddings($classLocator),        // Recognize embeddable entities
			new Entities($classLocator),          // Identify attributed entities
			new TableInheritance(),               // Setup Single Table or Joined Table Inheritance
			new MergeColumns(),                   // Integrate table #[Column] attributes
			new GenerateRelations(),       // Define entity relationships
			new GenerateModifiers(),       // Apply schema modifications
			new ValidateEntities(),        // Ensure entity schemas adhere to conventions
			new RenderTables(),            // Create table schemas
			new RenderRelations(),         // Establish keys and indexes for relationships
			new RenderModifiers(),         // Implement schema modifications
			new ForeignKeys(),             // Define foreign key constraints
			new MergeIndexes(),                   // Merge table index attributes
			new GenerateTypecast(),        // Typecast non-string columns
		]);

		$this->orm = new ORM(new Factory($dbal), new Schema($schema));
	}

	public function getOrm(): ORM
	{
		return $this->orm;
	}

	public function getMigrator(): Migrator
	{
		return $this->migrator;
	}

	public function getRegistry(): Registry
	{
		return $this->registry;
	}
}