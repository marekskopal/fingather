<?php

declare(strict_types=1);

namespace FinGather\App\ServiceProvider;

use FinGather\Mcp\McpUserContext;
use FinGather\Mcp\McpUserContextInterface;
use FinGather\Mcp\Server\FinGatherServer;
use FinGather\OAuth\AuthorizationService;
use FinGather\OAuth\AuthorizationServiceInterface;
use FinGather\OAuth\ClientService;
use FinGather\OAuth\ClientServiceInterface;
use FinGather\Service\Goal\GoalChecker;
use FinGather\Service\Goal\GoalCheckerInterface;
use FinGather\Service\Provider\ApiImportPrepareCheckProvider;
use FinGather\Service\Provider\ApiImportPrepareCheckProviderInterface;
use FinGather\Service\Provider\ApiImportProcessCheckProvider;
use FinGather\Service\Provider\ApiImportProcessCheckProviderInterface;
use FinGather\Service\Provider\ApiImportProvider;
use FinGather\Service\Provider\ApiImportProviderInterface;
use FinGather\Service\Provider\ApiKeyProvider;
use FinGather\Service\Provider\ApiKeyProviderInterface;
use FinGather\Service\Provider\AssetDataProvider;
use FinGather\Service\Provider\AssetDataProviderInterface;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\AssetWithPropertiesProvider;
use FinGather\Service\Provider\AssetWithPropertiesProviderInterface;
use FinGather\Service\Provider\BenchmarkAssetProvider;
use FinGather\Service\Provider\BenchmarkAssetProviderInterface;
use FinGather\Service\Provider\BenchmarkDataProvider;
use FinGather\Service\Provider\BenchmarkDataProviderInterface;
use FinGather\Service\Provider\BrokerProvider;
use FinGather\Service\Provider\BrokerProviderInterface;
use FinGather\Service\Provider\CalculatedGroupDataProvider;
use FinGather\Service\Provider\CalculatedGroupDataProviderInterface;
use FinGather\Service\Provider\CountryDataProvider;
use FinGather\Service\Provider\CountryDataProviderInterface;
use FinGather\Service\Provider\CountryProvider;
use FinGather\Service\Provider\CountryProviderInterface;
use FinGather\Service\Provider\CountryWithCountryDataProvider;
use FinGather\Service\Provider\CountryWithCountryDataProviderInterface;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\CurrencyProviderInterface;
use FinGather\Service\Provider\CurrentTransactionProvider;
use FinGather\Service\Provider\CurrentTransactionProviderInterface;
use FinGather\Service\Provider\DataProvider;
use FinGather\Service\Provider\DataProviderInterface;
use FinGather\Service\Provider\DcaPlanProvider;
use FinGather\Service\Provider\DcaPlanProviderInterface;
use FinGather\Service\Provider\DividendCalendarProvider;
use FinGather\Service\Provider\DividendCalendarProviderInterface;
use FinGather\Service\Provider\DividendDataProvider;
use FinGather\Service\Provider\DividendDataProviderInterface;
use FinGather\Service\Provider\EmailVerifyProvider;
use FinGather\Service\Provider\EmailVerifyProviderInterface;
use FinGather\Service\Provider\ExchangeRateProvider;
use FinGather\Service\Provider\ExchangeRateProviderInterface;
use FinGather\Service\Provider\GoalProvider;
use FinGather\Service\Provider\GoalProviderInterface;
use FinGather\Service\Provider\GroupDataProvider;
use FinGather\Service\Provider\GroupDataProviderInterface;
use FinGather\Service\Provider\GroupProvider;
use FinGather\Service\Provider\GroupProviderInterface;
use FinGather\Service\Provider\GroupWithGroupDataProvider;
use FinGather\Service\Provider\GroupWithGroupDataProviderInterface;
use FinGather\Service\Provider\ImportFileProvider;
use FinGather\Service\Provider\ImportFileProviderInterface;
use FinGather\Service\Provider\ImportMappingProvider;
use FinGather\Service\Provider\ImportMappingProviderInterface;
use FinGather\Service\Provider\ImportProvider;
use FinGather\Service\Provider\ImportProviderInterface;
use FinGather\Service\Provider\IndustryDataProvider;
use FinGather\Service\Provider\IndustryDataProviderInterface;
use FinGather\Service\Provider\IndustryProvider;
use FinGather\Service\Provider\IndustryProviderInterface;
use FinGather\Service\Provider\IndustryWithIndustryDataProvider;
use FinGather\Service\Provider\IndustryWithIndustryDataProviderInterface;
use FinGather\Service\Provider\PasswordResetProvider;
use FinGather\Service\Provider\PasswordResetProviderInterface;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\PortfolioDataProviderInterface;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Provider\PriceAlertProvider;
use FinGather\Service\Provider\PriceAlertProviderInterface;
use FinGather\Service\Provider\SectorDataProvider;
use FinGather\Service\Provider\SectorDataProviderInterface;
use FinGather\Service\Provider\SectorProvider;
use FinGather\Service\Provider\SectorProviderInterface;
use FinGather\Service\Provider\SectorWithSectorDataProvider;
use FinGather\Service\Provider\SectorWithSectorDataProviderInterface;
use FinGather\Service\Provider\SplitProvider;
use FinGather\Service\Provider\SplitProviderInterface;
use FinGather\Service\Provider\StrategyComparisonProvider;
use FinGather\Service\Provider\StrategyComparisonProviderInterface;
use FinGather\Service\Provider\StrategyProvider;
use FinGather\Service\Provider\StrategyProviderInterface;
use FinGather\Service\Provider\StrategyRebalancingProvider;
use FinGather\Service\Provider\StrategyRebalancingProviderInterface;
use FinGather\Service\Provider\TickerDataProvider;
use FinGather\Service\Provider\TickerDataProviderInterface;
use FinGather\Service\Provider\TickerFundamentalProvider;
use FinGather\Service\Provider\TickerFundamentalProviderInterface;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Provider\TickerProviderInterface;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Service\Provider\TransactionProviderInterface;
use FinGather\Service\Provider\UserProvider;
use FinGather\Service\Provider\UserProviderInterface;
use FinGather\Service\Request\RequestService;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Service\Task\TaskService;
use FinGather\Service\Task\TaskServiceInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

final class DomainServiceProvider extends AbstractServiceProvider
{
	public function provides(string $id): bool
	{
		return in_array($id, [
			RequestServiceInterface::class,
			TaskServiceInterface::class,
			ApiImportPrepareCheckProviderInterface::class,
			ApiImportProcessCheckProviderInterface::class,
			ApiImportProviderInterface::class,
			ApiKeyProviderInterface::class,
			AssetDataProviderInterface::class,
			AssetProviderInterface::class,
			AssetWithPropertiesProviderInterface::class,
			BenchmarkAssetProviderInterface::class,
			BenchmarkDataProviderInterface::class,
			BrokerProviderInterface::class,
			CalculatedGroupDataProviderInterface::class,
			CountryDataProviderInterface::class,
			CountryProviderInterface::class,
			CountryWithCountryDataProviderInterface::class,
			CurrencyProviderInterface::class,
			CurrentTransactionProviderInterface::class,
			DataProviderInterface::class,
			DcaPlanProviderInterface::class,
			DividendCalendarProviderInterface::class,
			DividendDataProviderInterface::class,
			EmailVerifyProviderInterface::class,
			ExchangeRateProviderInterface::class,
			GoalProviderInterface::class,
			GroupDataProviderInterface::class,
			GroupProviderInterface::class,
			GroupWithGroupDataProviderInterface::class,
			ImportFileProviderInterface::class,
			ImportMappingProviderInterface::class,
			ImportProviderInterface::class,
			IndustryDataProviderInterface::class,
			IndustryProviderInterface::class,
			IndustryWithIndustryDataProviderInterface::class,
			PasswordResetProviderInterface::class,
			PortfolioDataProviderInterface::class,
			PortfolioProviderInterface::class,
			PriceAlertProviderInterface::class,
			SectorDataProviderInterface::class,
			SectorProviderInterface::class,
			SectorWithSectorDataProviderInterface::class,
			SplitProviderInterface::class,
			StrategyComparisonProviderInterface::class,
			StrategyProviderInterface::class,
			StrategyRebalancingProviderInterface::class,
			TickerDataProviderInterface::class,
			TickerFundamentalProviderInterface::class,
			TickerProviderInterface::class,
			TransactionProviderInterface::class,
			UserProviderInterface::class,
			GoalCheckerInterface::class,
			AuthorizationServiceInterface::class,
			ClientServiceInterface::class,
			McpUserContextInterface::class,
			FinGatherServer::class,
		], true);
	}

	public function register(): void
	{
		$container = $this->getContainer();

		$container->add(RequestServiceInterface::class, fn () => new RequestService());
		$container->add(TaskServiceInterface::class, fn () => new TaskService());

		$container->add(ApiImportPrepareCheckProviderInterface::class, ApiImportPrepareCheckProvider::class);
		$container->add(ApiImportProcessCheckProviderInterface::class, ApiImportProcessCheckProvider::class);
		$container->add(ApiImportProviderInterface::class, ApiImportProvider::class);
		$container->add(ApiKeyProviderInterface::class, ApiKeyProvider::class);
		$container->add(AssetDataProviderInterface::class, AssetDataProvider::class);
		$container->add(AssetProviderInterface::class, AssetProvider::class);
		$container->add(AssetWithPropertiesProviderInterface::class, AssetWithPropertiesProvider::class);
		$container->add(BenchmarkAssetProviderInterface::class, BenchmarkAssetProvider::class);
		$container->add(BenchmarkDataProviderInterface::class, BenchmarkDataProvider::class);
		$container->add(BrokerProviderInterface::class, BrokerProvider::class);
		$container->add(CalculatedGroupDataProviderInterface::class, CalculatedGroupDataProvider::class);
		$container->add(CountryDataProviderInterface::class, CountryDataProvider::class);
		$container->add(CountryProviderInterface::class, CountryProvider::class);
		$container->add(CountryWithCountryDataProviderInterface::class, CountryWithCountryDataProvider::class);
		$container->add(CurrencyProviderInterface::class, CurrencyProvider::class);
		$container->add(CurrentTransactionProviderInterface::class, CurrentTransactionProvider::class);
		$container->add(DataProviderInterface::class, DataProvider::class);
		$container->add(DcaPlanProviderInterface::class, DcaPlanProvider::class);
		$container->add(DividendCalendarProviderInterface::class, DividendCalendarProvider::class);
		$container->add(DividendDataProviderInterface::class, DividendDataProvider::class);
		$container->add(EmailVerifyProviderInterface::class, EmailVerifyProvider::class);
		$container->add(ExchangeRateProviderInterface::class, ExchangeRateProvider::class);
		$container->add(GoalProviderInterface::class, GoalProvider::class);
		$container->add(GroupDataProviderInterface::class, GroupDataProvider::class);
		$container->add(GroupProviderInterface::class, GroupProvider::class);
		$container->add(GroupWithGroupDataProviderInterface::class, GroupWithGroupDataProvider::class);
		$container->add(ImportFileProviderInterface::class, ImportFileProvider::class);
		$container->add(ImportMappingProviderInterface::class, ImportMappingProvider::class);
		$container->add(ImportProviderInterface::class, ImportProvider::class);
		$container->add(IndustryDataProviderInterface::class, IndustryDataProvider::class);
		$container->add(IndustryProviderInterface::class, IndustryProvider::class);
		$container->add(IndustryWithIndustryDataProviderInterface::class, IndustryWithIndustryDataProvider::class);
		$container->add(AuthorizationServiceInterface::class, AuthorizationService::class);
		$container->add(ClientServiceInterface::class, ClientService::class);
		$container->add(PasswordResetProviderInterface::class, PasswordResetProvider::class);
		$container->add(PortfolioDataProviderInterface::class, PortfolioDataProvider::class);
		$container->add(PortfolioProviderInterface::class, PortfolioProvider::class);
		$container->add(PriceAlertProviderInterface::class, PriceAlertProvider::class);
		$container->add(SectorDataProviderInterface::class, SectorDataProvider::class);
		$container->add(SectorProviderInterface::class, SectorProvider::class);
		$container->add(SectorWithSectorDataProviderInterface::class, SectorWithSectorDataProvider::class);
		$container->add(SplitProviderInterface::class, SplitProvider::class);
		$container->add(StrategyComparisonProviderInterface::class, StrategyComparisonProvider::class);
		$container->add(StrategyProviderInterface::class, StrategyProvider::class);
		$container->add(StrategyRebalancingProviderInterface::class, StrategyRebalancingProvider::class);
		$container->add(TickerDataProviderInterface::class, TickerDataProvider::class);
		$container->add(TickerFundamentalProviderInterface::class, TickerFundamentalProvider::class);
		$container->add(TickerProviderInterface::class, TickerProvider::class);
		$container->add(TransactionProviderInterface::class, TransactionProvider::class);
		$container->add(UserProviderInterface::class, UserProvider::class);

		$container->add(GoalCheckerInterface::class, GoalChecker::class);

		$container->add(McpUserContextInterface::class, McpUserContext::class);

		$container->add(FinGatherServer::class, fn () => new FinGatherServer($container));
	}
}
