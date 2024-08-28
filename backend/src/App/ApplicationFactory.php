<?php

declare(strict_types=1);

namespace FinGather\App;

use Cycle\ORM\ORM;
use FinGather\Cache\Cache;
use FinGather\Middleware\AuthorizationMiddleware;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\AssetData;
use FinGather\Model\Entity\BenchmarkData;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\CountryData;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\EmailVerify;
use FinGather\Model\Entity\ExchangeRate;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\GroupData;
use FinGather\Model\Entity\Import;
use FinGather\Model\Entity\ImportFile;
use FinGather\Model\Entity\ImportMapping;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\IndustryData;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\PortfolioData;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\SectorData;
use FinGather\Model\Entity\Split;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\TickerData;
use FinGather\Model\Entity\TickerFundamental;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\AssetDataRepository;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Model\Repository\BenchmarkDataRepository;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CountryDataRepository;
use FinGather\Model\Repository\CountryRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\EmailVerifyRepository;
use FinGather\Model\Repository\ExchangeRateRepository;
use FinGather\Model\Repository\GroupDataRepository;
use FinGather\Model\Repository\GroupRepository;
use FinGather\Model\Repository\ImportFileRepository;
use FinGather\Model\Repository\ImportMappingRepository;
use FinGather\Model\Repository\ImportRepository;
use FinGather\Model\Repository\IndustryDataRepository;
use FinGather\Model\Repository\IndustryRepository;
use FinGather\Model\Repository\MarketRepository;
use FinGather\Model\Repository\PortfolioDataRepository;
use FinGather\Model\Repository\PortfolioRepository;
use FinGather\Model\Repository\SectorDataRepository;
use FinGather\Model\Repository\SectorRepository;
use FinGather\Model\Repository\SplitRepository;
use FinGather\Model\Repository\TickerDataRepository;
use FinGather\Model\Repository\TickerFundamentalRepository;
use FinGather\Model\Repository\TickerRepository;
use FinGather\Model\Repository\TransactionRepository;
use FinGather\Model\Repository\UserRepository;
use FinGather\Route\Strategy\JsonStrategy;
use FinGather\Service\Dbal\DbContext;
use FinGather\Service\Logger\Logger;
use FinGather\Service\Request\RequestService;
use FinGather\Service\Request\RequestServiceInterface;
use Http\Discovery\Psr17FactoryDiscovery;
use League\Container\Container;
use League\Container\ReflectionContainer;
use MarekSkopal\BuggregatorClient\Middleware\XhprofMiddleware;
use MarekSkopal\OpenFigi\OpenFigi;
use MarekSkopal\Router\Builder\RouterBuilder;
use MarekSkopal\TwelveData\Config\Config;
use MarekSkopal\TwelveData\TwelveData;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final class ApplicationFactory
{
	public static function create(): Application
	{
		$dbContext = self::initializeDbContext();
		$container = self::initializeContainer($dbContext);
		$requestHandler = self::initializeRequestHandler($container);

		return new Application($container, $requestHandler, $dbContext);
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

		$container->add(ORM::class, $dbContext->getOrm());

		$orm = $container->get(ORM::class);
		assert($orm instanceof ORM);

		$container->add(AssetRepository::class, fn () => $orm->getRepository(Asset::class));
		$container->add(AssetDataRepository::class, fn () => $orm->getRepository(AssetData::class));
		$container->add(BenchmarkDataRepository::class, fn () => $orm->getRepository(BenchmarkData::class));
		$container->add(BrokerRepository::class, fn () => $orm->getRepository(Broker::class));
		$container->add(CountryRepository::class, fn () => $orm->getRepository(Country::class));
		$container->add(CountryDataRepository::class, fn () => $orm->getRepository(CountryData::class));
		$container->add(CurrencyRepository::class, fn () => $orm->getRepository(Currency::class));
		$container->add(EmailVerifyRepository::class, fn () => $orm->getRepository(EmailVerify::class));
		$container->add(ExchangeRateRepository::class, fn () => $orm->getRepository(ExchangeRate::class));
		$container->add(GroupRepository::class, fn () => $orm->getRepository(Group::class));
		$container->add(GroupDataRepository::class, fn () => $orm->getRepository(GroupData::class));
		$container->add(IndustryRepository::class, fn () => $orm->getRepository(Industry::class));
		$container->add(IndustryDataRepository::class, fn () => $orm->getRepository(IndustryData::class));
		$container->add(ImportRepository::class, fn () => $orm->getRepository(Import::class));
		$container->add(ImportFileRepository::class, fn () => $orm->getRepository(ImportFile::class));
		$container->add(ImportMappingRepository::class, fn () => $orm->getRepository(ImportMapping::class));
		$container->add(MarketRepository::class, fn () => $orm->getRepository(Market::class));
		$container->add(PortfolioRepository::class, fn () => $orm->getRepository(Portfolio::class));
		$container->add(PortfolioDataRepository::class, fn () => $orm->getRepository(PortfolioData::class));
		$container->add(SectorRepository::class, fn () => $orm->getRepository(Sector::class));
		$container->add(SectorDataRepository::class, fn () => $orm->getRepository(SectorData::class));
		$container->add(SplitRepository::class, fn () => $orm->getRepository(Split::class));
		$container->add(TickerDataRepository::class, fn () => $orm->getRepository(TickerData::class));
		$container->add(TickerFundamentalRepository::class, fn () => $orm->getRepository(TickerFundamental::class));
		$container->add(TickerRepository::class, fn () => $orm->getRepository(Ticker::class));
		$container->add(TransactionRepository::class, fn () => $orm->getRepository(Transaction::class));
		$container->add(UserRepository::class, fn () => $orm->getRepository(User::class));

		$container->add(RequestServiceInterface::class, fn () => new RequestService());

		return $container;
	}

	private static function initializeRequestHandler(ContainerInterface $container): RequestHandlerInterface
	{
		$strategy = $container->get(JsonStrategy::class);
		assert($strategy instanceof JsonStrategy);
		$strategy->setContainer($container);

		$router = (new RouterBuilder())
			->setClassDirectories([__DIR__ . '/../Controller'])
			->setCache(new Cache())
			->build();

		$router->setStrategy($strategy);

		if ((bool) getenv('PROFILER_ENABLE') === true) {
			$xhprofMiddleware = $container->get(XhprofMiddleware::class);
			assert($xhprofMiddleware instanceof XhprofMiddleware);
			$router->middleware($xhprofMiddleware);
		}

		$authorizationMiddleware = $container->get(AuthorizationMiddleware::class);
		assert($authorizationMiddleware instanceof AuthorizationMiddleware);
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

		return new DbContext(dsn: 'mysql:host=' . $host . ';dbname=' . $database, user: $user, password: $password);
	}
}
