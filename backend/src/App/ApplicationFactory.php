<?php

declare(strict_types=1);

namespace FinGather\App;

use FinGather\Middleware\AuthorizationMiddleware;
use FinGather\Model\Entity\ApiImport;
use FinGather\Model\Entity\ApiKey;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\BenchmarkAsset;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\DcaPlan;
use FinGather\Model\Entity\EmailVerify;
use FinGather\Model\Entity\ExchangeRate;
use FinGather\Model\Entity\Goal;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Import;
use FinGather\Model\Entity\ImportFile;
use FinGather\Model\Entity\ImportMapping;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\PasswordReset;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\PriceAlert;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\Split;
use FinGather\Model\Entity\Strategy;
use FinGather\Model\Entity\StrategyItem;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\TickerData;
use FinGather\Model\Entity\TickerFundamental;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\ApiImportRepository;
use FinGather\Model\Repository\ApiKeyRepository;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Model\Repository\BenchmarkAssetRepository;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CountryRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\DcaPlanRepository;
use FinGather\Model\Repository\EmailVerifyRepository;
use FinGather\Model\Repository\ExchangeRateRepository;
use FinGather\Model\Repository\GoalRepository;
use FinGather\Model\Repository\GroupRepository;
use FinGather\Model\Repository\ImportFileRepository;
use FinGather\Model\Repository\ImportMappingRepository;
use FinGather\Model\Repository\ImportRepository;
use FinGather\Model\Repository\IndustryRepository;
use FinGather\Model\Repository\MarketRepository;
use FinGather\Model\Repository\PasswordResetRepository;
use FinGather\Model\Repository\PortfolioRepository;
use FinGather\Model\Repository\PriceAlertRepository;
use FinGather\Model\Repository\SectorRepository;
use FinGather\Model\Repository\SplitRepository;
use FinGather\Model\Repository\StrategyItemRepository;
use FinGather\Model\Repository\StrategyRepository;
use FinGather\Model\Repository\TickerDataRepository;
use FinGather\Model\Repository\TickerFundamentalRepository;
use FinGather\Model\Repository\TickerRepository;
use FinGather\Model\Repository\TransactionRepository;
use FinGather\Model\Repository\UserRepository;
use FinGather\Route\Strategy\JsonStrategy;
use FinGather\Service\Authentication\AuthenticationService;
use FinGather\Service\Authentication\AuthenticationServiceInterface;
use FinGather\Service\Authentication\GoogleAuthService;
use FinGather\Service\Authentication\GoogleAuthServiceInterface;
use FinGather\Service\Cache\CacheFactory;
use FinGather\Service\Cache\CacheFactoryInterface;
use FinGather\Service\DataCalculator\AssetDataCalculator;
use FinGather\Service\DataCalculator\AssetDataCalculatorInterface;
use FinGather\Service\DataCalculator\DataCalculator;
use FinGather\Service\DataCalculator\DataCalculatorInterface;
use FinGather\Service\DataCalculator\DcaPlanDataCalculator;
use FinGather\Service\DataCalculator\TaxReportRealizedGainsCalculator;
use FinGather\Service\DataCalculator\TaxReportRealizedGainsCalculatorInterface;
use FinGather\Service\Dbal\DbContext;
use FinGather\Service\Goal\GoalChecker;
use FinGather\Service\Goal\GoalCheckerInterface;
use FinGather\Service\Import\Factory\ImportMapperFactory;
use FinGather\Service\Import\Factory\ImportMapperFactoryInterface;
use FinGather\Service\Import\Factory\TransactionRecordFactory;
use FinGather\Service\Import\Factory\TransactionRecordFactoryInterface;
use FinGather\Service\Logger\Logger;
use FinGather\Service\Provider\CurrentTransactionProvider;
use FinGather\Service\Provider\DcaPlanProvider;
use FinGather\Service\Provider\DcaPlanProviderInterface;
use FinGather\Service\Provider\ExchangeRateProvider;
use FinGather\Service\Provider\GoalProvider;
use FinGather\Service\Provider\GoalProviderInterface;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\SplitProvider;
use FinGather\Service\Provider\SubscriptionProvider;
use FinGather\Service\Provider\TickerDataProvider;
use FinGather\Service\Provider\UserProvider;
use FinGather\Service\Request\RequestService;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Service\Task\TaskService;
use FinGather\Service\Task\TaskServiceInterface;
use FinGather\Service\Translator\TranslatorService;
use FinGather\Service\Translator\TranslatorServiceInterface;
use GuzzleHttp\Client as GuzzleClient;
use Http\Discovery\Psr17FactoryDiscovery;
use League\Container\Container;
use League\Container\ReflectionContainer;
use MarekSkopal\BuggregatorClient\Middleware\XhprofMiddleware;
use MarekSkopal\OpenFigi\OpenFigi;
use MarekSkopal\ORM\Database\DatabaseInterface;
use MarekSkopal\ORM\ORM;
use MarekSkopal\ORM\Repository\RepositoryInterface;
use MarekSkopal\Router\Builder\RouterBuilder;
use MarekSkopal\TwelveData\Config\Config;
use MarekSkopal\TwelveData\TwelveData;
use Predis\Client;
use Predis\ClientInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final class ApplicationFactory
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

		$container->add(LoggerInterface::class, fn () => Logger::initLogger(__DIR__ . '/../../log'));

		if ((bool) getenv('PROFILER_ENABLE') === true) {
			$container->add(XhprofMiddleware::class, fn () => new XhprofMiddleware(
				appName: 'FinGather',
				url: (string) getenv('PROFILER_ENDPOINT'),
			));
		}

		$container->add(
			ResponseFactoryInterface::class,
			fn (): ResponseFactoryInterface => Psr17FactoryDiscovery::findResponseFactory(),
		);

		$container->add(
			TwelveData::class,
			fn (): TwelveData => new TwelveData(new Config((string) getenv('TWELVEDATA_API_KEY'))),
		);

		$openfigiApiKey = (string) getenv('OPENFIGI_API_KEY');
		$container->add(
			OpenFigi::class,
			fn (): OpenFigi => new OpenFigi(new \MarekSkopal\OpenFigi\Config\Config($openfigiApiKey !== '' ? $openfigiApiKey : null)),
		);

		$container->add(
			ClientInterface::class,
			fn (): ClientInterface => new Client('tcp://' . getenv('REDIS_HOST') . ':' . getenv('REDIS_PORT'), [
				'parameters' => [
					'password' => (string) getenv('REDIS_PASSWORD'),
				],
			]),
		);

		self::initializeOrmContainer($container, $dbContext);

		$container->add(TranslatorServiceInterface::class, static function () use ($container): TranslatorService {
			$cacheFactory = $container->get(CacheFactoryInterface::class);
			if (!$cacheFactory instanceof CacheFactoryInterface) {
				throw new \RuntimeException('CacheFactory not found in container.');
			}
			return new TranslatorService(
				translationsDir: __DIR__ . '/../../translations',
				cache: $cacheFactory->create(namespace: 'translator'),
			);
		});

		$container->add(GuzzleClient::class, fn () => new GuzzleClient());
		$container->add(AuthenticationServiceInterface::class, AuthenticationService::class)
			->addArgument(UserProvider::class);
		$container->add(GoogleAuthServiceInterface::class, GoogleAuthService::class)
			->addArgument(GuzzleClient::class);
		$container->add(CacheFactoryInterface::class, CacheFactory::class)
			->addArgument(ClientInterface::class);
		$container->add(AssetDataCalculatorInterface::class, AssetDataCalculator::class)
			->addArguments(
				[CurrentTransactionProvider::class, SplitProvider::class, TickerDataProvider::class, ExchangeRateProvider::class],
			);
		$container->add(DataCalculatorInterface::class, DataCalculator::class);
		$container->add(TaxReportRealizedGainsCalculatorInterface::class, TaxReportRealizedGainsCalculator::class)
			->addArguments([CurrentTransactionProvider::class, SplitProvider::class]);
		$container->add(RequestServiceInterface::class, fn () => new RequestService());
		$container->add(TaskServiceInterface::class, fn () => new TaskService());
		$container->add(DcaPlanProviderInterface::class, DcaPlanProvider::class)
			->addArguments([DcaPlanRepository::class, DcaPlanDataCalculator::class]);
		$container->add(GoalProviderInterface::class, GoalProvider::class)
			->addArgument(GoalRepository::class);
		$container->add(GoalCheckerInterface::class, GoalChecker::class)
			->addArgument(PortfolioDataProvider::class);
		$container->add(ImportMapperFactoryInterface::class, ImportMapperFactory::class);
		$container->add(TransactionRecordFactoryInterface::class, TransactionRecordFactory::class);

		return $container;
	}

	private static function initializeOrmContainer(Container $container, DbContext $dbContext): void
	{
		$container->add(DatabaseInterface::class, fn () => $dbContext->getDatabase());
		$container->add(ORM::class, $dbContext->getOrm());

		$orm = $container->get(ORM::class);
		if (!$orm instanceof ORM) {
			throw new \RuntimeException('ORM not found in container.');
		}

		self::addRepository($container, $orm, ApiImportRepository::class, ApiImport::class);
		self::addRepository($container, $orm, ApiKeyRepository::class, ApiKey::class);
		self::addRepository($container, $orm, AssetRepository::class, Asset::class);
		self::addRepository($container, $orm, BenchmarkAssetRepository::class, BenchmarkAsset::class);
		self::addRepository($container, $orm, BrokerRepository::class, Broker::class);
		self::addRepository($container, $orm, CountryRepository::class, Country::class);
		self::addRepository($container, $orm, CurrencyRepository::class, Currency::class);
		self::addRepository($container, $orm, DcaPlanRepository::class, DcaPlan::class);
		self::addRepository($container, $orm, EmailVerifyRepository::class, EmailVerify::class);
		self::addRepository($container, $orm, ExchangeRateRepository::class, ExchangeRate::class);
		self::addRepository($container, $orm, GoalRepository::class, Goal::class);
		self::addRepository($container, $orm, GroupRepository::class, Group::class);
		self::addRepository($container, $orm, IndustryRepository::class, Industry::class);
		self::addRepository($container, $orm, ImportRepository::class, Import::class);
		self::addRepository($container, $orm, ImportFileRepository::class, ImportFile::class);
		self::addRepository($container, $orm, ImportMappingRepository::class, ImportMapping::class);
		self::addRepository($container, $orm, MarketRepository::class, Market::class);
		self::addRepository($container, $orm, PasswordResetRepository::class, PasswordReset::class);
		self::addRepository($container, $orm, PortfolioRepository::class, Portfolio::class);
		self::addRepository($container, $orm, PriceAlertRepository::class, PriceAlert::class);
		self::addRepository($container, $orm, SectorRepository::class, Sector::class);
		self::addRepository($container, $orm, SplitRepository::class, Split::class);
		self::addRepository($container, $orm, StrategyItemRepository::class, StrategyItem::class);
		self::addRepository($container, $orm, StrategyRepository::class, Strategy::class);
		self::addRepository($container, $orm, TickerDataRepository::class, TickerData::class);
		self::addRepository($container, $orm, TickerFundamentalRepository::class, TickerFundamental::class);
		self::addRepository($container, $orm, TickerRepository::class, Ticker::class);
		self::addRepository($container, $orm, TransactionRepository::class, Transaction::class);
		self::addRepository($container, $orm, UserRepository::class, User::class);
	}

	/**
	 * @param class-string<RepositoryInterface<TEntity>> $repositoryClass
	 * @param class-string<TEntity> $entityClass
	 * @template TEntity of object
	 */
	private static function addRepository(Container $container, ORM $orm, string $repositoryClass, string $entityClass): void
	{
		$repository = $orm->getRepository($entityClass);
		$container->add($repositoryClass, fn () => $repository);
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
