<?php

declare(strict_types=1);

namespace FinGather\Tests\Controller;

use FinGather\Controller\PortfolioRiskDataController;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Dto\PortfolioRiskDataDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Response\NotFoundResponse;
use FinGather\Service\DataCalculator\Dto\RiskDataDto;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Provider\RiskDataProviderInterface;
use FinGather\Service\Provider\TickerProviderInterface;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(PortfolioRiskDataController::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(User::class)]
#[UsesClass(NotFoundResponse::class)]
#[UsesClass(RiskDataDto::class)]
#[UsesClass(PortfolioRiskDataDto::class)]
final class PortfolioRiskDataControllerTest extends TestCase
{
	private RiskDataProviderInterface&Stub $riskDataProvider;

	private PortfolioProviderInterface&Stub $portfolioProvider;

	private TickerProviderInterface&Stub $tickerProvider;

	private RequestServiceInterface&Stub $requestService;

	private PortfolioRiskDataController $controller;

	protected function setUp(): void
	{
		$this->riskDataProvider = $this::createStub(RiskDataProviderInterface::class);
		$this->portfolioProvider = $this::createStub(PortfolioProviderInterface::class);
		$this->tickerProvider = $this::createStub(TickerProviderInterface::class);
		$this->requestService = $this::createStub(RequestServiceInterface::class);
		$this->requestService->method('getUser')->willReturn(UserFixture::getUser());

		$this->controller = new PortfolioRiskDataController(
			riskDataProvider: $this->riskDataProvider,
			portfolioProvider: $this->portfolioProvider,
			tickerProvider: $this->tickerProvider,
			requestService: $this->requestService,
		);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testActionGetPortfolioRiskDataInvalidIdReturnsNotFound(int $portfolioId): void
	{
		$request = $this::createStub(ServerRequestInterface::class);
		$request->method('getQueryParams')->willReturn(['range' => RangeEnum::OneYear->value]);

		$response = $this->controller->actionGetPortfolioRiskData($request, $portfolioId);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testActionGetPortfolioRiskDataNotFoundReturnsNotFound(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(null);

		$request = $this::createStub(ServerRequestInterface::class);
		$request->method('getQueryParams')->willReturn(['range' => RangeEnum::OneYear->value]);

		$response = $this->controller->actionGetPortfolioRiskData($request, 1);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testActionGetPortfolioRiskDataReturnsJsonResponse(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->riskDataProvider->method('getRiskData')->willReturn($this->makeRiskDataDto());

		$request = $this::createStub(ServerRequestInterface::class);
		$request->method('getQueryParams')->willReturn(['range' => RangeEnum::OneYear->value]);

		$response = $this->controller->actionGetPortfolioRiskData($request, 1);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	public function testActionGetPortfolioRiskDataWithBenchmarkTickerReturnsJsonResponse(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->tickerProvider->method('getTicker')->willReturn(TickerFixture::getTicker());
		$this->riskDataProvider->method('getRiskData')->willReturn($this->makeRiskDataDto());

		$request = $this::createStub(ServerRequestInterface::class);
		$request->method('getQueryParams')->willReturn([
			'range' => RangeEnum::OneYear->value,
			'benchmarkTickerId' => '1',
		]);

		$response = $this->controller->actionGetPortfolioRiskData($request, 1);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	private function makeRiskDataDto(): RiskDataDto
	{
		return new RiskDataDto(
			volatility: 18.5,
			maxDrawdown: -25.3,
			sharpeRatio: 0.87,
			beta: 1.12,
			correlationLabels: ['AAPL', 'MSFT'],
			correlationMatrix: [[1.0, 0.75], [0.75, 1.0]],
		);
	}
}
