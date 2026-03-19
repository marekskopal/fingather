<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Update;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Split;
use FinGather\Service\Provider\SplitProviderInterface;
use FinGather\Service\Update\SplitUpdater;
use FinGather\Tests\Fixtures\Model\Entity\TickerFixture;
use MarekSkopal\TwelveData\Api\Fundamentals;
use MarekSkopal\TwelveData\Dto\Fundamentals\Meta;
use MarekSkopal\TwelveData\Dto\Fundamentals\Splits;
use MarekSkopal\TwelveData\Dto\Fundamentals\SplitsSplit;
use MarekSkopal\TwelveData\Exception\NotFoundException;
use MarekSkopal\TwelveData\TwelveData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

#[CoversClass(SplitUpdater::class)]
#[UsesClass(Splits::class)]
#[UsesClass(SplitsSplit::class)]
#[UsesClass(Split::class)]
#[UsesClass(Decimal::class)]
final class SplitUpdaterTest extends TestCase
{
	private function makeMeta(): Meta
	{
		return new Meta(
			symbol: 'AAPL',
			name: 'Apple Inc.',
			currency: 'USD',
			exchange: 'NYSE',
			micCode: 'XNYS',
			exchangeTimezone: 'America/New_York',
		);
	}

	private function makeSplitsSplit(string $date = '2021-06-01', float $fromFactor = 4.0, float $toFactor = 1.0,): SplitsSplit
	{
		return new SplitsSplit(
			date: new DateTimeImmutable($date),
			description: 'Split',
			ratio: $fromFactor / $toFactor,
			fromFactor: $fromFactor,
			toFactor: $toFactor,
		);
	}

	private function makeSplitsDto(SplitsSplit ...$splits): Splits
	{
		return new Splits(meta: $this->makeMeta(), splits: $splits);
	}

	private function makeUpdater(SplitProviderInterface $splitProvider, TwelveData $twelveData): SplitUpdater
	{
		return new SplitUpdater($splitProvider, $twelveData);
	}

	private function makeTwelveDataStub(Splits $splitsDto): TwelveData
	{
		$fundamentalsStub = $this->createStub(Fundamentals::class);
		$fundamentalsStub->method('splits')->willReturn($splitsDto);

		$twelveDataStub = $this->createStub(TwelveData::class);

		// TwelveData is a `readonly class`, so its `fundamentals` property cannot be set externally.
		// We use ReflectionProperty to initialise the uninitialized readonly property on the stub.
		$prop = new ReflectionProperty(TwelveData::class, 'fundamentals');
		$prop->setValue($twelveDataStub, $fundamentalsStub);

		return $twelveDataStub;
	}

	public function testUpdateFetchesSplitsFromApiAndSaves(): void
	{
		$ticker = TickerFixture::getTicker();
		$split = $this->makeSplitsSplit(date: '2021-06-01', fromFactor: 4.0, toFactor: 1.0);
		$splitsDto = $this->makeSplitsDto($split);

		$splitProvider = $this->createMock(SplitProviderInterface::class);
		// not a duplicate
		$splitProvider->method('getSplit')->willReturn(null);
		$splitProvider->expects(self::once())->method('createSplit')->with(
			$ticker,
			$split->date,
			self::callback(fn (Decimal $factor): bool => $factor->toString() === '4'),
		);
		$splitProvider->expects(self::once())->method('cleanCache')->with($ticker);

		$updater = $this->makeUpdater($splitProvider, $this->makeTwelveDataStub($splitsDto));
		$updater->updateSplits($ticker);
	}

	public function testUpdateSkipsDuplicateSplits(): void
	{
		$ticker = TickerFixture::getTicker();
		$split = $this->makeSplitsSplit();
		$splitsDto = $this->makeSplitsDto($split);

		$existingSplit = new Split(
			tickerId: $ticker->id,
			date: $split->date,
			factor: new Decimal(4),
		);

		$splitProvider = $this->createMock(SplitProviderInterface::class);
		// already exists
		$splitProvider->method('getSplit')->willReturn($existingSplit);
		$splitProvider->expects(self::never())->method('createSplit');
		$splitProvider->expects(self::never())->method('cleanCache');

		$updater = $this->makeUpdater($splitProvider, $this->makeTwelveDataStub($splitsDto));
		$updater->updateSplits($ticker);
	}

	public function testUpdateHandlesNotFoundExceptionGracefully(): void
	{
		$ticker = TickerFixture::getTicker();

		$fundamentalsStub = $this->createStub(Fundamentals::class);
		$fundamentalsStub->method('splits')->willThrowException(new NotFoundException('Not found'));

		$twelveDataStub = $this->createStub(TwelveData::class);
		$prop = new ReflectionProperty(TwelveData::class, 'fundamentals');
		$prop->setValue($twelveDataStub, $fundamentalsStub);

		$splitProvider = $this->createMock(SplitProviderInterface::class);
		$splitProvider->expects(self::never())->method('createSplit');

		$updater = $this->makeUpdater($splitProvider, $twelveDataStub);
		// should not throw
		$updater->updateSplits($ticker);
	}

	public function testUpdateCalculatesSplitFactor(): void
	{
		$ticker = TickerFixture::getTicker();
		$split = $this->makeSplitsSplit(fromFactor: 3.0, toFactor: 1.0);
		$splitsDto = $this->makeSplitsDto($split);

		$splitProvider = $this->createMock(SplitProviderInterface::class);
		$splitProvider->method('getSplit')->willReturn(null);

		$capturedFactor = null;
		$splitProvider->method('createSplit')
			->willReturnCallback(function ($t, $d, Decimal $factor) use (&$capturedFactor): Split {
				$capturedFactor = $factor;
				return new Split(tickerId: $t->id, date: $d, factor: $factor);
			});
		$splitProvider->method('cleanCache');

		$updater = $this->makeUpdater($splitProvider, $this->makeTwelveDataStub($splitsDto));
		$updater->updateSplits($ticker);

		self::assertNotNull($capturedFactor);
		// fromFactor / toFactor = 3.0 / 1.0 = 3
		self::assertSame('3', $capturedFactor->toString());
	}

	public function testUpdateDoesNotCleanCacheWhenNoNewSplits(): void
	{
		$ticker = TickerFixture::getTicker();
		// empty splits
		$splitsDto = $this->makeSplitsDto();

		$splitProvider = $this->createMock(SplitProviderInterface::class);
		$splitProvider->expects(self::never())->method('cleanCache');

		$updater = $this->makeUpdater($splitProvider, $this->makeTwelveDataStub($splitsDto));
		$updater->updateSplits($ticker);
	}
}
