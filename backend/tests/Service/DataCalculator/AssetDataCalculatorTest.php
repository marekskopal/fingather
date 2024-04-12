<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator;

use Decimal\Decimal;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Service\DataCalculator\AssetDataCalculator;
use FinGather\Service\Provider\ExchangeRateProvider;
use FinGather\Service\Provider\SplitProvider;
use FinGather\Service\Provider\TickerDataProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TransactionFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;

#[CoversClass(AssetDataCalculator::class)]
class AssetDataCalculatorTest extends TestCase
{
	public function testCalculateNull(): void
	{
		$transactions = [];

		$assetDataCalculator = $this->createAssetDataCalculator($transactions);

		$user = UserFixture::getUser();
		$portfolio = PortfolioFixture::getPortfolio();
		$asset = AssetFixture::getAsset();
		$dateTime = new DateTimeImmutable();

		$this->assertNull($assetDataCalculator->calculate(
			$user,
			$portfolio,
			$asset,
			$dateTime,
		));
	}

	public function testCalculate(): void
	{
		$transactions = [
			TransactionFixture::getTransaction(actionType: TransactionActionTypeEnum::Buy, units: new Decimal(1)),
			TransactionFixture::getTransaction(actionType: TransactionActionTypeEnum::Sell, units: new Decimal(-1)),
		];

		$assetDataCalculator = $this->createAssetDataCalculator($transactions);

		$user = UserFixture::getUser();
		$portfolio = PortfolioFixture::getPortfolio();
		$asset = AssetFixture::getAsset();
		$dateTime = new DateTimeImmutable();

		$assetData = $assetDataCalculator->calculate($user, $portfolio, $asset, $dateTime);

		$this->assertSame(0.0, $assetData->value->toFloat());
		$this->assertSame(0.0, $assetData->transactionValue->toFloat());
		$this->assertSame(0.0, $assetData->transactionValueDefaultCurrency->toFloat());
		$this->assertSame(0.0, $assetData->units->toFloat());
		$this->assertSame(0.0, $assetData->dividendGain->toFloat());
		$this->assertSame(0.0, $assetData->dividendGainDefaultCurrency->toFloat());
	}

	private function createAssetDataCalculator(array $transactions): AssetDataCalculator
	{
		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactions')
			->willReturn($transactions);

		$splitProvider = $this->createMock(SplitProvider::class);
		$splitProvider->method('getSplits')
			->willReturn([]);

		$tickerDataProvider = $this->createStub(TickerDataProvider::class);
		$exchangeRateProvider = $this->createMock(ExchangeRateProvider::class);
		$exchangeRateProvider->method('getExchangeRate')
			->willReturn(new Decimal(1));

		return new AssetDataCalculator($transactionProvider, $splitProvider, $tickerDataProvider, $exchangeRateProvider);
	}
}
