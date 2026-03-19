<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Update;

use FinGather\Model\Entity\Enum\MarketTypeEnum;
use FinGather\Model\Repository\TickerRepository;
use FinGather\Service\Update\TickerLogoUpdater;
use FinGather\Tests\Fixtures\Model\Entity\MarketFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerFixture;
use MarekSkopal\TwelveData\Api\Fundamentals;
use MarekSkopal\TwelveData\Dto\Fundamentals\Logo;
use MarekSkopal\TwelveData\Dto\Fundamentals\LogoMeta;
use MarekSkopal\TwelveData\Exception\NotFoundException;
use MarekSkopal\TwelveData\TwelveData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(TickerLogoUpdater::class)]
final class TickerLogoUpdaterTest extends TestCase
{
	/**
	 * TickerRepository is final so it cannot be mocked with createMock/createStub.
	 * We instantiate it without a constructor via reflection — persist() will throw if
	 * accidentally called (uninitialised properties), which acts as an implicit assertion.
	 */
	private function makeTickerRepository(): TickerRepository
	{
		return (new ReflectionClass(TickerRepository::class))->newInstanceWithoutConstructor();
	}

	private function makeLogo(?string $url, ?string $logoBase = null): Logo
	{
		return new Logo(
			meta: new LogoMeta(symbol: 'AAPL', exchange: 'NYSE'),
			url: $url,
			logoBase: $logoBase,
			logoQuote: null,
		);
	}

	public function testSkipsWhenLogoAlreadySetAndNotApiDir(): void
	{
		$ticker = TickerFixture::getTicker(logo: 'aapl.svg');
		$logoBeforeUpdate = $ticker->logo;

		$twelveData = $this->createStub(TwelveData::class);
		// TwelveData must not be called — if it were, getFundamentals() would return a stub that returns null for logo()
		// and the method would reach persist(), which would throw because the repo is uninitialized.
		$updater = new TickerLogoUpdater($this->makeTickerRepository(), $twelveData);
		$updater->updateTickerLogo($ticker);

		// Logo unchanged confirms early return happened
		self::assertSame($logoBeforeUpdate, $ticker->logo);
	}

	public function testHandlesNotFoundExceptionGracefully(): void
	{
		$ticker = TickerFixture::getTicker();
		// TickerFixture defaults to a non-null logo; override it
		$ticker->logo = null;

		$fundamentalsStub = $this->createStub(Fundamentals::class);
		$fundamentalsStub->method('logo')->willThrowException(new NotFoundException('Not found'));

		$twelveData = $this->createStub(TwelveData::class);
		$twelveData->method('getFundamentals')->willReturn($fundamentalsStub);

		$updater = new TickerLogoUpdater($this->makeTickerRepository(), $twelveData);
		// should not throw
		$updater->updateTickerLogo($ticker);

		// Logo not set because exception was caught
		self::assertNull($ticker->logo);
	}

	public function testHandlesLogoWithNoUrlGracefully(): void
	{
		$ticker = TickerFixture::getTicker();
		$ticker->logo = null;

		$logo = $this->makeLogo(url: null, logoBase: null);

		$fundamentalsStub = $this->createStub(Fundamentals::class);
		$fundamentalsStub->method('logo')->willReturn($logo);

		$twelveData = $this->createStub(TwelveData::class);
		$twelveData->method('getFundamentals')->willReturn($fundamentalsStub);

		$updater = new TickerLogoUpdater($this->makeTickerRepository(), $twelveData);
		// should not throw
		$updater->updateTickerLogo($ticker);

		// Logo not set because URL was null
		self::assertNull($ticker->logo);
	}

	public function testUsesSymbolForStockTicker(): void
	{
		$ticker = TickerFixture::getTicker(ticker: 'AAPL');
		$ticker->logo = null;

		$capturedSymbol = null;
		$capturedMicCode = null;

		$fundamentalsStub = $this->createStub(Fundamentals::class);
		$fundamentalsStub->method('logo')->willReturnCallback(
			function (string $symbol, ?string $exchange = null, ?string $micCode = null) use (&$capturedSymbol, &$capturedMicCode): never {
				$capturedSymbol = $symbol;
				$capturedMicCode = $micCode;

				throw new NotFoundException('Not found');
			},
		);

		$twelveData = $this->createStub(TwelveData::class);
		$twelveData->method('getFundamentals')->willReturn($fundamentalsStub);

		$updater = new TickerLogoUpdater($this->makeTickerRepository(), $twelveData);
		$updater->updateTickerLogo($ticker);

		self::assertSame('AAPL', $capturedSymbol);
		self::assertSame('XNYS', $capturedMicCode);
	}

	public function testCryptoTickerAppendsCurrencySuffix(): void
	{
		$cryptoMarket = MarketFixture::getMarket(type: MarketTypeEnum::Crypto, mic: 'CRYPTO');
		$ticker = TickerFixture::getTicker(ticker: 'BTC', market: $cryptoMarket);
		$ticker->logo = null;

		$capturedSymbol = null;

		$fundamentalsStub = $this->createStub(Fundamentals::class);
		$fundamentalsStub->method('logo')->willReturnCallback(
			function (string $symbol) use (&$capturedSymbol): never {
				$capturedSymbol = $symbol;

				throw new NotFoundException('Not found');
			},
		);

		$twelveData = $this->createStub(TwelveData::class);
		$twelveData->method('getFundamentals')->willReturn($fundamentalsStub);

		$updater = new TickerLogoUpdater($this->makeTickerRepository(), $twelveData);
		$updater->updateTickerLogo($ticker);

		self::assertSame('BTC/USD', $capturedSymbol);
	}
}
