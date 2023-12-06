<?php

declare(strict_types=1);

namespace FinGather\App;

use Cycle\Annotated\Embeddings;
use Cycle\Annotated\Entities;
use Cycle\Annotated\MergeColumns;
use Cycle\Annotated\MergeIndexes;
use Cycle\Annotated\TableInheritance;
use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\Config\MySQL\DsnConnectionConfig;
use Cycle\Database\Config\MySQLDriverConfig;
use Cycle\Database\DatabaseManager;
use Cycle\ORM\Factory;
use Cycle\ORM\ORM;
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
use Cycle\Schema\Generator\SyncTables;
use Cycle\Schema\Generator\ValidateEntities;
use Cycle\Schema\Registry;
use Doctrine\Common\Annotations\AnnotationRegistry;
use FinGather\Route\Routes;
use Http\Discovery\Psr17FactoryDiscovery;
use League\Container\Container;
use League\Container\ReflectionContainer;
use League\Route\Router;
use League\Route\Strategy\JsonStrategy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\Finder\Finder;

class Application
{
	public function __construct(RoadRunnerProcessor $psr7Processor)
	{
		try {
			$container = $this->initContainer();
			$router = $this->initRouter($container);

			//$dbAdapter = $container->get(DbAdapter::class);
			//assert($dbAdapter instanceof DbAdapter);
			$logger = $container->get(LoggerInterface::class);
			assert($logger instanceof LoggerInterface);

			$psr7Processor($router, $logger);
		} catch (\Throwable $e) { // @phpstan-ignore-line 'Throwable' must be rethrown (nowhere to rethrow)
			$psr7Processor->handleException($e, $logger ?? null, null);
		}
	}

	private function initContainer(): ContainerInterface
	{
		$container = new Container();
		$container->defaultToShared();
		$container->delegate((new ReflectionContainer(true)));

		$container->add(LoggerInterface::class, fn () => RoadRunnerLogger::initLogger(__DIR__ . '/../../log'));

		$container->add(
			ResponseFactoryInterface::class,
			fn (): ResponseFactoryInterface => Psr17FactoryDiscovery::findResponseFactory()
		);

		$container->add(ORM::class, $this->initOrm());

		return $container;
	}

	private function initRouter(ContainerInterface $container): RequestHandlerInterface
	{
		$strategy = $container->get(JsonStrategy::class);
		assert($strategy instanceof JsonStrategy);
		$strategy->setContainer($container);

		$router = new Router();
		$router->setStrategy($strategy);

		$routeList = Routes::getRouteList();
		$routeList->setRouteListToRouter($router);

		return $router;
	}

	private function initOrm(): ORM
	{
		$dbal = new DatabaseManager(
			new DatabaseConfig([
				'default' => 'default',
				'databases' => [
					'default' => ['connection' => 'sqlite']
				],
				'connections' => [
					'sqlite' => new MySQLDriverConfig(
						connection: new DsnConnectionConfig(
							dsn: 'mysql:host=db;dbname=fingather',
							user: 'fingather',
							password: 'fingather',
						)
					),
				]
			])
		);

		$finder = (new Finder())->files()->in([
			__DIR__ . '/../Model/Entity',
		]); // __DIR__ here is folder with entities
		$classLocator = new ClassLocator($finder);

		// Initialize annotations
		//AnnotationRegistry::registerLoader('class_exists');

		$schema = (new Compiler())->compile(new Registry($dbal), [
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
			new SyncTables(),              // Align table changes with the database
			new GenerateTypecast(),        // Typecast non-string columns
		]);

		return new ORM(new Factory($dbal), new Schema($schema));
	}
}
