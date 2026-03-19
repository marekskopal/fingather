<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Update;

use FinGather\Model\Repository\MarketRepository;
use FinGather\Model\Repository\TickerRepository;
use FinGather\Service\Update\TickerIsinUpdater;
use MarekSkopal\OpenFigi\Dto\FigiResult;
use MarekSkopal\OpenFigi\Dto\MappingJobResult;
use MarekSkopal\OpenFigi\OpenFigi;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Note: TickerRepository and MarketRepository are declared `final` and cannot be mocked via
 * PHPUnit's createMock/createStub. We instantiate them without their constructor via reflection
 * (newInstanceWithoutConstructor). Any accidental method call on these will throw an Error
 * due to uninitialised properties — acting as an implicit "never called" assertion.
 * Tests that require the repositories to return specific values are covered by integration tests.
 */
#[CoversClass(TickerIsinUpdater::class)]
final class TickerIsinUpdaterTest extends TestCase
{
	private function makeTickerRepository(): TickerRepository
	{
		return (new ReflectionClass(TickerRepository::class))->newInstanceWithoutConstructor();
	}

	private function makeMarketRepository(): MarketRepository
	{
		return (new ReflectionClass(MarketRepository::class))->newInstanceWithoutConstructor();
	}

	private function makeUpdater(OpenFigi $openFigi): TickerIsinUpdater
	{
		return new TickerIsinUpdater(
			$this->makeTickerRepository(),
			$this->makeMarketRepository(),
			$openFigi,
		);
	}

	private function makeFigiResult(?string $ticker, ?string $exchCode): FigiResult
	{
		return new FigiResult(
			figi: 'BBG000B9XRY4',
			securityType: 'Common Stock',
			marketSector: 'Equity',
			ticker: $ticker,
			name: 'Test Corp',
			exchCode: $exchCode,
			shareClassFIGI: null,
			compositeFIGI: null,
			securityType2: 'Common Stock',
			securityDescription: 'CS',
			metadata: null,
		);
	}

	public function testUpdateHandlesEmptyIsinsArray(): void
	{
		$openFigi = $this->createMock(OpenFigi::class);
		$openFigi->method('getMaxJobsPerRequest')->willReturn(10);
		$openFigi->expects(self::never())->method('mapping');

		$this->makeUpdater($openFigi)->updateTickerIsins([]);
	}

	public function testUpdateSkipsWhenMappingResultDataIsNull(): void
	{
		$mappingResult = new MappingJobResult(data: null, warning: 'No data');

		$openFigi = $this->createStub(OpenFigi::class);
		$openFigi->method('getMaxJobsPerRequest')->willReturn(10);
		$openFigi->method('mapping')->willReturn([$mappingResult]);

		// Repos are reflection-instantiated; any accidental call would throw an Error.
		$this->makeUpdater($openFigi)->updateTickerIsins(['US0000000000']);

		// reaching this line means no exception was thrown
		$this->addToAssertionCount(1);
	}

	public function testUpdateSkipsWhenFigiResultExchCodeIsNull(): void
	{
		$figiResult = $this->makeFigiResult(ticker: 'AAPL', exchCode: null);
		$mappingResult = new MappingJobResult(data: [$figiResult], warning: null);

		$openFigi = $this->createStub(OpenFigi::class);
		$openFigi->method('getMaxJobsPerRequest')->willReturn(10);
		$openFigi->method('mapping')->willReturn([$mappingResult]);

		// Repos are reflection-instantiated; any call to findMarketByExchangeCode would throw.
		$this->makeUpdater($openFigi)->updateTickerIsins(['US0378331005']);

		$this->addToAssertionCount(1);
	}

	public function testUpdateSkipsWhenFigiResultTickerIsNull(): void
	{
		$figiResult = $this->makeFigiResult(ticker: null, exchCode: 'US');
		$mappingResult = new MappingJobResult(data: [$figiResult], warning: null);

		$openFigi = $this->createStub(OpenFigi::class);
		$openFigi->method('getMaxJobsPerRequest')->willReturn(10);
		$openFigi->method('mapping')->willReturn([$mappingResult]);

		// Repos are reflection-instantiated; any call to findMarketByExchangeCode would throw.
		$this->makeUpdater($openFigi)->updateTickerIsins(['US0378331005']);

		$this->addToAssertionCount(1);
	}

	public function testUpdateCallsMappingWithCorrectIsins(): void
	{
		$capturedJobs = null;

		$openFigi = $this->createStub(OpenFigi::class);
		$openFigi->method('getMaxJobsPerRequest')->willReturn(10);
		$openFigi->method('mapping')->willReturnCallback(
			function (array $jobs) use (&$capturedJobs): array {
				$capturedJobs = $jobs;
				// Return result with null data so repos are never called
				return [new MappingJobResult(data: null, warning: null)];
			},
		);

		$this->makeUpdater($openFigi)->updateTickerIsins(['US0378331005']);

		self::assertNotNull($capturedJobs);
		self::assertCount(1, $capturedJobs);
		self::assertSame('US0378331005', $capturedJobs[0]->idValue);
	}
}
