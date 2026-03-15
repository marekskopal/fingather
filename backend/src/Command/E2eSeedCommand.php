<?php

declare(strict_types=1);

namespace FinGather\Command;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\App\ApplicationFactory;
use FinGather\Model\Entity\Enum\AlertConditionEnum;
use FinGather\Model\Entity\Enum\AlertRecurrenceEnum;
use FinGather\Model\Entity\Enum\DcaPlanTargetTypeEnum;
use FinGather\Model\Entity\Enum\GoalTypeEnum;
use FinGather\Model\Entity\Enum\LocaleEnum;
use FinGather\Model\Entity\Enum\PriceAlertTypeEnum;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Enum\TransactionCreateTypeEnum;
use FinGather\Model\Entity\Enum\UserPlanEnum;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\DcaPlanProvider;
use FinGather\Service\Provider\GoalProvider;
use FinGather\Service\Provider\GroupProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\PriceAlertProvider;
use FinGather\Service\Provider\StrategyProvider;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Service\Provider\UserProvider;
use PDO;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class E2eSeedCommand extends AbstractCommand
{
	private const string TestEmail = 'test@fingather.test';
	private const string TestPassword = 'Test1234!';
	private const string TestName = 'E2E Test User';

	private const int TickerAapl = 1679;
	private const int TickerMsft = 2664;
	private const int TickerNvda = 2722;

	protected function configure(): void
	{
		$this->setName('e2e:seed');
		$this->setDescription('Run migrations and seed the database with E2E test data (idempotent)');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		// 1. Run migrations
		$this->writeln('Running migrations...', $output);
		$application->dbContext->getMigrator()->migrate();
		$application->dbContext->clearCache();

		$pdo = $application->dbContext->getDatabase()->getPdo();

		// 2. Seed reference data (sectors, industries, tickers) via raw SQL so
		//    no TwelveData API calls are triggered
		$this->writeln('Seeding reference data...', $output);
		$this->seedReferenceData($pdo);

		// 3. Resolve providers
		$userProvider = $application->container->get(UserProvider::class);
		assert($userProvider instanceof UserProvider);

		$currencyProvider = $application->container->get(CurrencyProvider::class);
		assert($currencyProvider instanceof CurrencyProvider);

		$portfolioProvider = $application->container->get(PortfolioProvider::class);
		assert($portfolioProvider instanceof PortfolioProvider);

		$groupProvider = $application->container->get(GroupProvider::class);
		assert($groupProvider instanceof GroupProvider);

		$assetRepository = $application->container->get(AssetRepository::class);
		assert($assetRepository instanceof AssetRepository);

		$tickerProvider = $application->container->get(TickerProvider::class);
		assert($tickerProvider instanceof TickerProvider);

		$transactionProvider = $application->container->get(TransactionProvider::class);
		assert($transactionProvider instanceof TransactionProvider);

		// 4. Delete existing test user (cascades to all owned data)
		$existing = $userProvider->getUserByEmail(self::TestEmail);
		if ($existing !== null) {
			$this->writeln('Deleting existing test user...', $output);
			$userProvider->deleteUser($existing);
		}

		// 5. Resolve USD currency
		$usd = null;
		foreach ($currencyProvider->getCurrencies() as $currency) {
			if ($currency->code === 'USD') {
				$usd = $currency;
				break;
			}
		}

		if ($usd === null) {
			$this->writeln('USD currency not found after migrations.', $output);
			return self::FAILURE;
		}

		// 6. Create test user (also creates default portfolio + others group via UserProvider)
		$this->writeln('Creating test user ' . self::TestEmail . '...', $output);
		$user = $userProvider->createUser(
			email: self::TestEmail,
			password: self::TestPassword,
			name: self::TestName,
			defaultCurrency: $usd,
			role: UserRoleEnum::User,
			isEmailVerified: true,
			locale: LocaleEnum::En,
		);

		$userProvider->onboardingCompleteUser($user);

		$portfolio = $portfolioProvider->getDefaultPortfolio($user);

		$othersGroup = $groupProvider->getOthersGroup($user, $portfolio);

		// 7. Insert assets directly via PDO to bypass TickerRelationsUpdater
		//    which would otherwise call the TwelveData splits API
		$this->writeln('Creating assets and transactions...', $output);

		$stmt = $pdo->prepare('INSERT INTO `assets` (`user_id`, `portfolio_id`, `ticker_id`, `group_id`) VALUES (?, ?, ?, ?)');
		if ($stmt === false) {
			throw new \RuntimeException('Failed to prepare asset insert statement.');
		}

		$aaplAsset = null;

		foreach ([self::TickerAapl, self::TickerMsft, self::TickerNvda] as $tickerId) {
			$ticker = $tickerProvider->getTicker($tickerId);
			if ($ticker === null) {
				$this->writeln('Warning: ticker ' . $tickerId . ' not found, skipping.', $output);
				continue;
			}

			$stmt->execute([$user->id, $portfolio->id, $ticker->id, $othersGroup->id]);

			$asset = $assetRepository->findAssetByTickerId(tickerId: $ticker->id, userId: $user->id, portfolioId: $portfolio->id);
			assert($asset !== null);

			if ($tickerId === self::TickerAapl) {
				$aaplAsset = $asset;
			}

			foreach ([
				new DateTimeImmutable('2023-06-01'),
				new DateTimeImmutable('2024-01-15'),
			] as $date) {
				$transactionProvider->createTransaction(
					user: $user,
					portfolio: $portfolio,
					asset: $asset,
					broker: null,
					actionType: TransactionActionTypeEnum::Buy,
					actionCreated: $date,
					createType: TransactionCreateTypeEnum::Manual,
					units: new Decimal('1'),
					price: new Decimal('150.00'),
					currency: $usd,
					tax: null,
					taxCurrency: $usd,
					fee: null,
					feeCurrency: $usd,
					notes: 'E2E test transaction',
					importIdentifier: null,
				);
			}
		}

		assert($aaplAsset !== null, 'AAPL asset must have been created');

		// 8. Seed a dividend transaction for AAPL (enables "edit dividend form loads" test)
		$this->writeln('Creating dividend transaction...', $output);
		$transactionProvider->createTransaction(
			user: $user,
			portfolio: $portfolio,
			asset: $aaplAsset,
			broker: null,
			actionType: TransactionActionTypeEnum::Dividend,
			actionCreated: new DateTimeImmutable('2024-06-01'),
			createType: TransactionCreateTypeEnum::Manual,
			units: new Decimal('0'),
			price: new Decimal('5.00'),
			currency: $usd,
			tax: null,
			taxCurrency: $usd,
			fee: null,
			feeCurrency: $usd,
			notes: 'E2E test dividend',
			importIdentifier: null,
		);

		// 9. Seed a custom group with AAPL (enables group edit/delete tests).
		//    MSFT and NVDA remain in "Others" so they are available for group-creation tests.
		$this->writeln('Creating group...', $output);
		$groupProvider->createGroup(
			user: $user,
			portfolio: $portfolio,
			name: 'E2E Tech Group',
			color: '#0d6efd',
			assetIds: [$aaplAsset->id],
		);

		// 10. Seed a goal (enables goal edit/delete tests)
		$this->writeln('Creating goal...', $output);
		$goalProvider = $application->container->get(GoalProvider::class);
		assert($goalProvider instanceof GoalProvider);
		$goalProvider->createGoal(
			user: $user,
			portfolio: $portfolio,
			type: GoalTypeEnum::PortfolioValue,
			targetValue: new Decimal('100000'),
			deadline: new DateTimeImmutable('2030-12-31'),
		);

		// 11. Seed a strategy (enables strategy edit/delete tests)
		$this->writeln('Creating strategy...', $output);
		$strategyProvider = $application->container->get(StrategyProvider::class);
		assert($strategyProvider instanceof StrategyProvider);
		$strategyProvider->createStrategy(
			user: $user,
			portfolio: $portfolio,
			name: 'E2E Default Strategy',
			isDefault: false,
			items: [],
		);

		// 12. Seed a DCA plan (enables DCA plan edit/delete tests)
		$this->writeln('Creating DCA plan...', $output);
		$dcaPlanProvider = $application->container->get(DcaPlanProvider::class);
		assert($dcaPlanProvider instanceof DcaPlanProvider);
		$dcaPlanProvider->createDcaPlan(
			user: $user,
			targetType: DcaPlanTargetTypeEnum::Portfolio,
			portfolio: $portfolio,
			asset: null,
			group: null,
			strategy: null,
			amount: new Decimal('500'),
			currency: $usd,
			intervalMonths: 1,
			startDate: new DateTimeImmutable('2025-01-01'),
			endDate: null,
		);

		// 13. Seed a price alert (enables price alert edit/delete tests)
		$this->writeln('Creating price alert...', $output);
		$priceAlertProvider = $application->container->get(PriceAlertProvider::class);
		assert($priceAlertProvider instanceof PriceAlertProvider);
		$priceAlertProvider->createPriceAlert(
			user: $user,
			type: PriceAlertTypeEnum::Portfolio,
			condition: AlertConditionEnum::Above,
			targetValue: '50000',
			recurrence: AlertRecurrenceEnum::Recurring,
			cooldownHours: 24,
			portfolio: $portfolio,
		);

		$this->writeln('E2E seed completed successfully.', $output);

		return self::SUCCESS;
	}

	private function seedReferenceData(PDO $pdo): void
	{
		// Sector: Technology (id=4)
		$pdo->exec(
			"INSERT IGNORE INTO `sectors` (`id`, `name`, `is_others`) VALUES
			(4, 'Technology', 0)",
		);

		// Industries needed by AAPL (4), NVDA (5), MSFT (10)
		$pdo->exec(
			"INSERT IGNORE INTO `industries` (`id`, `name`, `is_others`) VALUES
			(4,  'Consumer Electronics',      0),
			(5,  'Semiconductors',            0),
			(10, 'Software - Infrastructure', 0)",
		);

		// Tickers: AAPL, MSFT, NVDA
		// market_id=3 (XNGS/NASDAQ) and country_id=226 (US) are seeded by InitData migration
		// currency_id=1 (USD) is seeded by InitData migration
		$pdo->exec(
			"INSERT IGNORE INTO `tickers`
				(`id`, `ticker`, `name`, `market_id`, `currency_id`, `type`, `isin`, `sector_id`, `industry_id`, `country_id`)
			VALUES
			(1679, 'AAPL', 'Apple Inc',      3, 1, 'Stock', 'US0378331005', 4,  4, 226),
			(2664, 'MSFT', 'Microsoft Corp', 3, 1, 'Stock', 'US5949181045', 4, 10, 226),
			(2722, 'NVDA', 'NVIDIA Corp',    3, 1, 'Stock', 'US67066G1040', 4,  5, 226)",
		);

		// Ticker price data — seeded so getLastTickerDataClose() finds prices without calling TwelveData.
		// Date is 3 days ago to always be <= the weekend-adjusted "today" in TickerDataProvider.
		$pdo->exec(
			"INSERT IGNORE INTO `ticker_datas`
				(`ticker_id`, `date`, `open`, `close`, `high`, `low`, `volume`)
			VALUES
			(1679, CURRENT_DATE - INTERVAL 3 DAY, '220.00', '222.50', '223.00', '219.50', '50000000'),
			(2664, CURRENT_DATE - INTERVAL 3 DAY, '415.00', '418.00', '420.00', '414.00', '20000000'),
			(2722, CURRENT_DATE - INTERVAL 3 DAY, '875.00', '880.00', '885.00', '872.00', '40000000')",
		);
	}
}
