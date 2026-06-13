<?php

declare(strict_types=1);

namespace FinGather\Tests\Model\Repository;

use FinGather\Model\Entity\Enum\TickerTypeEnum;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Repository\TickerRepository;
use MarekSkopal\ORM\Database\SqliteDatabase;
use MarekSkopal\ORM\ORM;
use MarekSkopal\ORM\Schema\Builder\SchemaBuilder;
use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TickerRepository::class)]
final class TickerRepositoryTest extends TestCase
{
	private PDO $pdo;

	private TickerRepository $repository;

	protected function setUp(): void
	{
		$database = new SqliteDatabase(':memory:');
		$this->pdo = $database->getPdo();
		$this->createSchema();

		$schema = new SchemaBuilder()
			->addEntityPath(__DIR__ . '/../../../src/Model/Entity')
			->build();

		$repository = new ORM($database, $schema)->getRepository(Ticker::class);
		assert($repository instanceof TickerRepository);
		$this->repository = $repository;
	}

	/**
	 * Regression test for ORM 1.2.0: findTickersMostUsed orders by `count(*)`, which the ORM only
	 * accepts wrapped in a RawExpression (a plain string throws InvalidArgumentException). A revert
	 * would break the /api/tickers/most-used endpoint and the add-asset ticker dropdown.
	 */
	public function testFindTickersMostUsedOrdersByUsageCountDescending(): void
	{
		$this->seedTicker(1, 'AAPL', 'Apple Inc');
		$this->seedTicker(2, 'MSFT', 'Microsoft Corporation');
		$this->seedTicker(3, 'NVDA', 'NVIDIA Corporation');

		$this->seedAssetsForTicker(tickerId: 1, count: 3);
		$this->seedAssetsForTicker(tickerId: 2, count: 2);
		$this->seedAssetsForTicker(tickerId: 3, count: 1);

		$tickers = $this->repository->findTickersMostUsed(20);

		$symbols = array_map(static fn (Ticker $ticker): string => $ticker->ticker, $tickers);
		self::assertSame(['AAPL', 'MSFT', 'NVDA'], $symbols);
	}

	public function testFindTickersMostUsedRespectsLimit(): void
	{
		$this->seedTicker(1, 'AAPL', 'Apple Inc');
		$this->seedTicker(2, 'MSFT', 'Microsoft Corporation');

		$this->seedAssetsForTicker(tickerId: 1, count: 2);
		$this->seedAssetsForTicker(tickerId: 2, count: 1);

		$tickers = $this->repository->findTickersMostUsed(1);

		self::assertCount(1, $tickers);
		self::assertSame('AAPL', $tickers[0]->ticker);
	}

	private function createSchema(): void
	{
		$this->pdo->exec(
			'CREATE TABLE tickers (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				ticker TEXT NOT NULL,
				name TEXT NOT NULL,
				market_id INTEGER NOT NULL,
				currency_id INTEGER NOT NULL,
				type TEXT NOT NULL,
				isin TEXT,
				logo TEXT,
				sector_id INTEGER NOT NULL,
				industry_id INTEGER NOT NULL,
				website TEXT,
				description TEXT,
				country_id INTEGER NOT NULL
			)',
		);

		$this->pdo->exec(
			'CREATE TABLE assets (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				user_id INTEGER NOT NULL,
				portfolio_id INTEGER NOT NULL,
				ticker_id INTEGER NOT NULL,
				group_id INTEGER NOT NULL
			)',
		);
	}

	private function seedTicker(int $id, string $ticker, string $name): void
	{
		$statement = $this->pdo->prepare(
			'INSERT INTO tickers (id, ticker, name, market_id, currency_id, type, sector_id, industry_id, country_id)
				VALUES (?, ?, ?, 1, 1, ?, 1, 1, 1)',
		);
		self::assertInstanceOf(PDOStatement::class, $statement);
		$statement->execute([$id, $ticker, $name, TickerTypeEnum::Stock->value]);
	}

	private function seedAssetsForTicker(int $tickerId, int $count): void
	{
		$statement = $this->pdo->prepare('INSERT INTO assets (user_id, portfolio_id, ticker_id, group_id) VALUES (1, 1, ?, 1)');
		self::assertInstanceOf(PDOStatement::class, $statement);
		for ($i = 0; $i < $count; $i++) {
			$statement->execute([$tickerId]);
		}
	}
}
