<?php

declare(strict_types=1);

namespace FinGather\App\ServiceProvider;

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
use FinGather\Model\Entity\OAuthAuthorization;
use FinGather\Model\Entity\OAuthClient;
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
use FinGather\Model\Repository\OAuthAuthorizationRepository;
use FinGather\Model\Repository\OAuthClientRepository;
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
use FinGather\Service\Dbal\DbContext;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use MarekSkopal\ORM\Database\DatabaseInterface;
use MarekSkopal\ORM\ORM;
use MarekSkopal\ORM\Repository\RepositoryInterface;

final class OrmServiceProvider extends AbstractServiceProvider
{
	public function __construct(private readonly DbContext $dbContext)
	{
	}

	public function provides(string $id): bool
	{
		return in_array($id, [
			DatabaseInterface::class,
			ORM::class,
			ApiImportRepository::class,
			ApiKeyRepository::class,
			AssetRepository::class,
			BenchmarkAssetRepository::class,
			BrokerRepository::class,
			CountryRepository::class,
			CurrencyRepository::class,
			DcaPlanRepository::class,
			EmailVerifyRepository::class,
			ExchangeRateRepository::class,
			GoalRepository::class,
			GroupRepository::class,
			ImportRepository::class,
			ImportFileRepository::class,
			ImportMappingRepository::class,
			IndustryRepository::class,
			MarketRepository::class,
			OAuthAuthorizationRepository::class,
			OAuthClientRepository::class,
			PasswordResetRepository::class,
			PortfolioRepository::class,
			PriceAlertRepository::class,
			SectorRepository::class,
			SplitRepository::class,
			StrategyItemRepository::class,
			StrategyRepository::class,
			TickerDataRepository::class,
			TickerFundamentalRepository::class,
			TickerRepository::class,
			TransactionRepository::class,
			UserRepository::class,
		], true);
	}

	public function register(): void
	{
		$container = $this->getContainer();
		assert($container instanceof Container);

		$container->add(DatabaseInterface::class, fn () => $this->dbContext->getDatabase());
		$container->add(ORM::class, $this->dbContext->getOrm());

		$orm = $this->dbContext->getOrm();

		$this->addRepository($container, $orm, ApiImportRepository::class, ApiImport::class);
		$this->addRepository($container, $orm, ApiKeyRepository::class, ApiKey::class);
		$this->addRepository($container, $orm, AssetRepository::class, Asset::class);
		$this->addRepository($container, $orm, BenchmarkAssetRepository::class, BenchmarkAsset::class);
		$this->addRepository($container, $orm, BrokerRepository::class, Broker::class);
		$this->addRepository($container, $orm, CountryRepository::class, Country::class);
		$this->addRepository($container, $orm, CurrencyRepository::class, Currency::class);
		$this->addRepository($container, $orm, DcaPlanRepository::class, DcaPlan::class);
		$this->addRepository($container, $orm, EmailVerifyRepository::class, EmailVerify::class);
		$this->addRepository($container, $orm, ExchangeRateRepository::class, ExchangeRate::class);
		$this->addRepository($container, $orm, GoalRepository::class, Goal::class);
		$this->addRepository($container, $orm, GroupRepository::class, Group::class);
		$this->addRepository($container, $orm, IndustryRepository::class, Industry::class);
		$this->addRepository($container, $orm, ImportRepository::class, Import::class);
		$this->addRepository($container, $orm, ImportFileRepository::class, ImportFile::class);
		$this->addRepository($container, $orm, ImportMappingRepository::class, ImportMapping::class);
		$this->addRepository($container, $orm, MarketRepository::class, Market::class);
		$this->addRepository($container, $orm, OAuthAuthorizationRepository::class, OAuthAuthorization::class);
		$this->addRepository($container, $orm, OAuthClientRepository::class, OAuthClient::class);
		$this->addRepository($container, $orm, PasswordResetRepository::class, PasswordReset::class);
		$this->addRepository($container, $orm, PortfolioRepository::class, Portfolio::class);
		$this->addRepository($container, $orm, PriceAlertRepository::class, PriceAlert::class);
		$this->addRepository($container, $orm, SectorRepository::class, Sector::class);
		$this->addRepository($container, $orm, SplitRepository::class, Split::class);
		$this->addRepository($container, $orm, StrategyItemRepository::class, StrategyItem::class);
		$this->addRepository($container, $orm, StrategyRepository::class, Strategy::class);
		$this->addRepository($container, $orm, TickerDataRepository::class, TickerData::class);
		$this->addRepository($container, $orm, TickerFundamentalRepository::class, TickerFundamental::class);
		$this->addRepository($container, $orm, TickerRepository::class, Ticker::class);
		$this->addRepository($container, $orm, TransactionRepository::class, Transaction::class);
		$this->addRepository($container, $orm, UserRepository::class, User::class);
	}

	/**
	 * @param class-string<RepositoryInterface<TEntity>> $repositoryClass
	 * @param class-string<TEntity> $entityClass
	 * @template TEntity of object
	 */
	private function addRepository(Container $container, ORM $orm, string $repositoryClass, string $entityClass): void
	{
		$repository = $orm->getRepository($entityClass);
		$container->add($repositoryClass, fn () => $repository);
	}
}
