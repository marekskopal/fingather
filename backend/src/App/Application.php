<?php

declare(strict_types=1);

namespace FinGather\App;

use Cycle\ORM\ORM;
use FinGather\Middleware\AuthorizationMiddleware;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\UserRepository;
use FinGather\Route\Routes;
use FinGather\Route\Strategy\JsonStrategy;
use FinGather\Service\Dbal\DbContext;
use Http\Discovery\Psr17FactoryDiscovery;
use League\Container\Container;
use League\Container\ReflectionContainer;
use League\Route\Router;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class Application
{
	public function __construct(RoadRunnerProcessor $psr7Processor)
	{
		try {
			$container = $this->initContainer();
			$router = $this->initRouter($container);

			$logger = $container->get(LoggerInterface::class);
			assert($logger instanceof LoggerInterface);

			$psr7Processor($router, $logger);
		} catch (\Throwable $e) {
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

		$orm = $container->get(ORM::class);
		assert($orm instanceof ORM);

		$container->add(BrokerRepository::class, fn () => $orm->getRepository(Broker::class));
		$container->add(UserRepository::class, fn () => $orm->getRepository(User::class));

		return $container;
	}

	private function initRouter(ContainerInterface $container): RequestHandlerInterface
	{
		$strategy = $container->get(JsonStrategy::class);
		assert($strategy instanceof JsonStrategy);
		$strategy->setContainer($container);

		$router = new Router();
		$router->setStrategy($strategy);

		$authorizationMiddleware = $container->get(AuthorizationMiddleware::class);
		assert($authorizationMiddleware instanceof AuthorizationMiddleware);
		$router->middleware($authorizationMiddleware);

		$routeList = Routes::getRouteList();
		$routeList->setRouteListToRouter($router);

		return $router;
	}

	private function initOrm(): ORM
	{
		$dbContext = new DbContext(dsn: 'mysql:host=db;dbname=fingather', user: 'fingather', password: 'fingather');

		return $dbContext->getOrm();
	}
}
