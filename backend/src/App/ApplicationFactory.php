<?php

declare(strict_types=1);

namespace FinGather\App;

use FinGather\App\ServiceProvider\AuthenticationServiceProvider;
use FinGather\App\ServiceProvider\CacheServiceProvider;
use FinGather\App\ServiceProvider\CalculatorServiceProvider;
use FinGather\App\ServiceProvider\DomainServiceProvider;
use FinGather\App\ServiceProvider\ImportServiceProvider;
use FinGather\App\ServiceProvider\InfrastructureServiceProvider;
use FinGather\App\ServiceProvider\OrmServiceProvider;
use FinGather\Middleware\AuthorizationMiddleware;
use FinGather\Route\Strategy\JsonStrategy;
use FinGather\Service\Cache\CacheFactory;
use FinGather\Service\Dbal\DbContext;
use League\Container\Container;
use League\Container\ReflectionContainer;
use MarekSkopal\BuggregatorClient\Middleware\XhprofMiddleware;
use MarekSkopal\Router\Builder\RouterBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class ApplicationFactory
{
	public static function create(): Application
	{
		self::validateEnvironment();

		$dbContext = self::initializeDbContext();
		$container = self::initializeContainer($dbContext);
		$requestHandler = self::initializeRequestHandler($container);

		return new Application($container, $requestHandler, $dbContext);
	}

	private static function validateEnvironment(): void
	{
		$required = [
			'AUTHORIZATION_TOKEN_KEY',
			'ENCRYPTION_KEY',
			'MYSQL_HOST',
			'MYSQL_DATABASE',
			'MYSQL_USER',
			'MYSQL_PASSWORD',
			'REDIS_HOST',
			'REDIS_PORT',
			'MEMCACHED_HOST',
			'MEMCACHED_PORT',
			'RABBITMQ_HOST',
			'RABBITMQ_PORT',
			'RABBITMQ_USER',
			'RABBITMQ_PASSWORD',
			'SMTP_HOST',
			'SMTP_PORT',
			'EMAIL_FROM',
			'PROXY_HOST',
			'PROXY_PORT_SSL',
		];

		$missing = [];
		foreach ($required as $var) {
			$value = getenv($var);
			if ($value === false || $value === '') {
				$missing[] = $var;
			}
		}

		if ($missing !== []) {
			throw new \RuntimeException('Required environment variables are not set: ' . implode(', ', $missing));
		}
	}

	private static function initializeContainer(DbContext $dbContext): ContainerInterface
	{
		$container = new Container();
		$container->defaultToShared();
		$container->delegate((new ReflectionContainer(true)));

		$container->addServiceProvider(new InfrastructureServiceProvider());
		$container->addServiceProvider(new OrmServiceProvider($dbContext));
		$container->addServiceProvider(new CacheServiceProvider());
		$container->addServiceProvider(new AuthenticationServiceProvider());
		$container->addServiceProvider(new CalculatorServiceProvider());
		$container->addServiceProvider(new ImportServiceProvider());
		$container->addServiceProvider(new DomainServiceProvider());

		return $container;
	}

	private static function initializeRequestHandler(ContainerInterface $container): RequestHandlerInterface
	{
		$strategy = $container->get(JsonStrategy::class);
		if (!$strategy instanceof JsonStrategy) {
			throw new \RuntimeException('JsonStrategy not found in container.');
		}
		$strategy->setContainer($container);

		$router = (new RouterBuilder())
			->setClassDirectories([__DIR__ . '/../Controller'])
			->setCache(CacheFactory::createPsrCache())
			->build();

		$router->setStrategy($strategy);

		if ((bool) getenv('PROFILER_ENABLE') === true) {
			$xhprofMiddleware = $container->get(XhprofMiddleware::class);
			if (!$xhprofMiddleware instanceof XhprofMiddleware) {
				throw new \RuntimeException('XhprofMiddleware not found in container.');
			}
			$router->middleware($xhprofMiddleware);
		}

		$authorizationMiddleware = $container->get(AuthorizationMiddleware::class);
		if (!$authorizationMiddleware instanceof AuthorizationMiddleware) {
			throw new \RuntimeException('AuthorizationMiddleware not found in container.');
		}
		$router->middleware($authorizationMiddleware);

		return $router;
	}

	private static function initializeDbContext(): DbContext
	{
		$host = (string) getenv('MYSQL_HOST');
		$database = (string) getenv('MYSQL_DATABASE');
		/** @var non-empty-string $user */
		$user = (string) getenv('MYSQL_USER');
		/** @var non-empty-string $password */
		$password = (string) getenv('MYSQL_PASSWORD');

		return new DbContext($host, $database, $user, $password);
	}
}
