<?php

declare(strict_types=1);

namespace FinGather\Tests\Controller;

use ArrayIterator;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Controller\DcaPlanController;
use FinGather\Dto\DcaPlanCreateDto;
use FinGather\Dto\DcaPlanDto;
use FinGather\Dto\DcaPlanProjectionDto;
use FinGather\Dto\DcaPlanUpdateDto;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\DcaPlan;
use FinGather\Model\Entity\Enum\DcaPlanTargetTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Service\DataCalculator\Dto\ReturnRateDto;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\CurrencyProviderInterface;
use FinGather\Service\Provider\DcaPlanProviderInterface;
use FinGather\Service\Provider\GroupProviderInterface;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Provider\StrategyProviderInterface;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Tests\Fixtures\Model\Entity\CurrencyFixture;
use FinGather\Tests\Fixtures\Model\Entity\DcaPlanFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use FinGather\Utils\DateTimeUtils;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(DcaPlanController::class)]
#[UsesClass(Currency::class)]
#[UsesClass(DcaPlan::class)]
#[UsesClass(DcaPlanCreateDto::class)]
#[UsesClass(DcaPlanProjectionDto::class)]
#[UsesClass(DcaPlanUpdateDto::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(User::class)]
#[UsesClass(NotFoundResponse::class)]
#[UsesClass(OkResponse::class)]
#[UsesClass(ReturnRateDto::class)]
#[UsesClass(DateTimeUtils::class)]
#[UsesClass(DcaPlanDto::class)]
final class DcaPlanControllerTest extends TestCase
{
	private DcaPlanProviderInterface&Stub $dcaPlanProvider;

	private PortfolioProviderInterface&Stub $portfolioProvider;

	private AssetProviderInterface&Stub $assetProvider;

	private GroupProviderInterface&Stub $groupProvider;

	private StrategyProviderInterface&Stub $strategyProvider;

	private CurrencyProviderInterface&Stub $currencyProvider;

	private RequestServiceInterface&Stub $requestService;

	private DcaPlanController $dcaPlanController;

	protected function setUp(): void
	{
		$this->dcaPlanProvider = $this::createStub(DcaPlanProviderInterface::class);
		$this->portfolioProvider = $this::createStub(PortfolioProviderInterface::class);
		$this->assetProvider = $this::createStub(AssetProviderInterface::class);
		$this->groupProvider = $this::createStub(GroupProviderInterface::class);
		$this->strategyProvider = $this::createStub(StrategyProviderInterface::class);
		$this->currencyProvider = $this::createStub(CurrencyProviderInterface::class);
		$this->requestService = $this::createStub(RequestServiceInterface::class);
		$this->requestService->method('getUser')->willReturn(UserFixture::getUser());
		$this->dcaPlanProvider->method('getReturnRate')->willReturn(new ReturnRateDto(annual: 7.0, monthly: 0.58));

		$this->dcaPlanController = new DcaPlanController(
			$this->dcaPlanProvider,
			$this->portfolioProvider,
			$this->assetProvider,
			$this->groupProvider,
			$this->strategyProvider,
			$this->currencyProvider,
			$this->requestService,
		);
	}

	public function testGetDcaPlansPortfolioNotFoundReturnsNotFound(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(null);

		$response = $this->dcaPlanController->actionGetDcaPlans(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testGetDcaPlansReturnsJsonResponse(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->dcaPlanProvider->method('getDcaPlans')->willReturn(new ArrayIterator([]));

		$response = $this->dcaPlanController->actionGetDcaPlans(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testGetDcaPlanInvalidIdReturnsNotFound(int $dcaPlanId): void
	{
		$response = $this->dcaPlanController->actionGetDcaPlan(
			$this::createStub(ServerRequestInterface::class),
			$dcaPlanId,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testGetDcaPlanNotFoundReturnsNotFound(): void
	{
		$this->dcaPlanProvider->method('getDcaPlan')->willReturn(null);

		$response = $this->dcaPlanController->actionGetDcaPlan(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testGetDcaPlanReturnsJsonResponse(): void
	{
		$dcaPlan = DcaPlanFixture::getDcaPlan();
		$this->dcaPlanProvider->method('getDcaPlan')->willReturn($dcaPlan);

		$response = $this->dcaPlanController->actionGetDcaPlan(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testPostDcaPlanInvalidPortfolioIdReturnsNotFound(int $portfolioId): void
	{
		$response = $this->dcaPlanController->actionPostDcaPlan(
			$this::createStub(ServerRequestInterface::class),
			$portfolioId,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testPutDcaPlanInvalidIdReturnsNotFound(int $dcaPlanId): void
	{
		$response = $this->dcaPlanController->actionPutDcaPlan(
			$this::createStub(ServerRequestInterface::class),
			$dcaPlanId,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPutDcaPlanNotFoundReturnsNotFound(): void
	{
		$this->dcaPlanProvider->method('getDcaPlan')->willReturn(null);

		$response = $this->dcaPlanController->actionPutDcaPlan(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testDeleteDcaPlanInvalidIdReturnsNotFound(int $dcaPlanId): void
	{
		$response = $this->dcaPlanController->actionDeleteDcaPlan(
			$this::createStub(ServerRequestInterface::class),
			$dcaPlanId,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testDeleteDcaPlanNotFoundReturnsNotFound(): void
	{
		$this->dcaPlanProvider->method('getDcaPlan')->willReturn(null);

		$response = $this->dcaPlanController->actionDeleteDcaPlan(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testDeleteDcaPlanReturnsOkResponse(): void
	{
		$dcaPlan = DcaPlanFixture::getDcaPlan();
		$this->dcaPlanProvider->method('getDcaPlan')->willReturn($dcaPlan);

		$response = $this->dcaPlanController->actionDeleteDcaPlan(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(OkResponse::class, $response);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testGetDcaPlanProjectionInvalidIdReturnsNotFound(int $dcaPlanId): void
	{
		$response = $this->dcaPlanController->actionGetDcaPlanProjection(
			$this::createStub(ServerRequestInterface::class),
			$dcaPlanId,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testGetDcaPlanProjectionNotFoundReturnsNotFound(): void
	{
		$this->dcaPlanProvider->method('getDcaPlan')->willReturn(null);

		$request = $this::createStub(ServerRequestInterface::class);
		$request->method('getQueryParams')->willReturn([]);

		$response = $this->dcaPlanController->actionGetDcaPlanProjection($request, 1);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	// --- actionPostDcaPlan happy paths and lookups ---

	public function testPostDcaPlanCurrencyNotFoundReturnsNotFound(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeCreateDto());
		$this->currencyProvider->method('getCurrency')->willReturn(null);

		$response = $this->dcaPlanController->actionPostDcaPlan($this::createStub(ServerRequestInterface::class), 1);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPostDcaPlanAssetNotFoundReturnsNotFound(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeCreateDto(assetId: 99));
		$this->assetProvider->method('getAsset')->willReturn(null);

		$response = $this->dcaPlanController->actionPostDcaPlan($this::createStub(ServerRequestInterface::class), 1);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPostDcaPlanGroupNotFoundReturnsNotFound(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeCreateDto(groupId: 99));
		$this->groupProvider->method('getGroup')->willReturn(null);

		$response = $this->dcaPlanController->actionPostDcaPlan($this::createStub(ServerRequestInterface::class), 1);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPostDcaPlanStrategyNotFoundReturnsNotFound(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeCreateDto(strategyId: 99));
		$this->strategyProvider->method('getStrategy')->willReturn(null);

		$response = $this->dcaPlanController->actionPostDcaPlan($this::createStub(ServerRequestInterface::class), 1);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPostDcaPlanReturnsJsonResponseOnSuccess(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeCreateDto());
		$this->currencyProvider->method('getCurrency')->willReturn(CurrencyFixture::getCurrency());
		$this->dcaPlanProvider->method('createDcaPlan')->willReturn(DcaPlanFixture::getDcaPlan());

		$response = $this->dcaPlanController->actionPostDcaPlan($this::createStub(ServerRequestInterface::class), 1);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	// --- actionPutDcaPlan happy paths and lookups ---

	public function testPutDcaPlanCurrencyNotFoundReturnsNotFound(): void
	{
		$this->dcaPlanProvider->method('getDcaPlan')->willReturn(DcaPlanFixture::getDcaPlan());
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeUpdateDto());
		$this->currencyProvider->method('getCurrency')->willReturn(null);

		$response = $this->dcaPlanController->actionPutDcaPlan($this::createStub(ServerRequestInterface::class), 1);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPutDcaPlanAssetNotFoundReturnsNotFound(): void
	{
		$this->dcaPlanProvider->method('getDcaPlan')->willReturn(DcaPlanFixture::getDcaPlan());
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeUpdateDto(assetId: 99));
		$this->assetProvider->method('getAsset')->willReturn(null);

		$response = $this->dcaPlanController->actionPutDcaPlan($this::createStub(ServerRequestInterface::class), 1);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPutDcaPlanGroupNotFoundReturnsNotFound(): void
	{
		$this->dcaPlanProvider->method('getDcaPlan')->willReturn(DcaPlanFixture::getDcaPlan());
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeUpdateDto(groupId: 99));
		$this->groupProvider->method('getGroup')->willReturn(null);

		$response = $this->dcaPlanController->actionPutDcaPlan($this::createStub(ServerRequestInterface::class), 1);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPutDcaPlanStrategyNotFoundReturnsNotFound(): void
	{
		$this->dcaPlanProvider->method('getDcaPlan')->willReturn(DcaPlanFixture::getDcaPlan());
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeUpdateDto(strategyId: 99));
		$this->strategyProvider->method('getStrategy')->willReturn(null);

		$response = $this->dcaPlanController->actionPutDcaPlan($this::createStub(ServerRequestInterface::class), 1);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPutDcaPlanReturnsJsonResponseOnSuccess(): void
	{
		$dcaPlan = DcaPlanFixture::getDcaPlan();
		$this->dcaPlanProvider->method('getDcaPlan')->willReturn($dcaPlan);
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeUpdateDto());
		$this->currencyProvider->method('getCurrency')->willReturn(CurrencyFixture::getCurrency());
		$this->dcaPlanProvider->method('updateDcaPlan')->willReturn($dcaPlan);

		$response = $this->dcaPlanController->actionPutDcaPlan($this::createStub(ServerRequestInterface::class), 1);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	// --- actionGetDcaPlanProjection happy path ---

	public function testGetDcaPlanProjectionUsesQueryParamsForHorizonAndCurrentValueToggle(): void
	{
		$dcaPlan = DcaPlanFixture::getDcaPlan();
		$this->dcaPlanProvider->method('getDcaPlan')->willReturn($dcaPlan);

		$capturedHorizon = null;
		$capturedWithCurrent = null;
		$this->dcaPlanProvider->method('getProjection')->willReturnCallback(
			function (DcaPlan $plan, int $horizonYears, bool $withCurrentValue) use (&$capturedHorizon, &$capturedWithCurrent): DcaPlanProjectionDto {
				$capturedHorizon = $horizonYears;
				$capturedWithCurrent = $withCurrentValue;
				return new DcaPlanProjectionDto(dataPoints: []);
			},
		);

		$request = $this::createStub(ServerRequestInterface::class);
		$request->method('getQueryParams')->willReturn(['horizonYears' => '25', 'withCurrentValue' => 'false']);

		$response = $this->dcaPlanController->actionGetDcaPlanProjection($request, 1);

		self::assertInstanceOf(JsonResponse::class, $response);
		self::assertSame(25, $capturedHorizon);
		self::assertFalse($capturedWithCurrent);
	}

	public function testGetDcaPlanProjectionDefaultsToTenYearsAndCurrentValueOn(): void
	{
		$dcaPlan = DcaPlanFixture::getDcaPlan();
		$this->dcaPlanProvider->method('getDcaPlan')->willReturn($dcaPlan);

		$capturedHorizon = null;
		$capturedWithCurrent = null;
		$this->dcaPlanProvider->method('getProjection')->willReturnCallback(
			function (DcaPlan $plan, int $horizonYears, bool $withCurrentValue) use (&$capturedHorizon, &$capturedWithCurrent): DcaPlanProjectionDto {
				$capturedHorizon = $horizonYears;
				$capturedWithCurrent = $withCurrentValue;
				return new DcaPlanProjectionDto(dataPoints: []);
			},
		);

		$request = $this::createStub(ServerRequestInterface::class);
		$request->method('getQueryParams')->willReturn([]);

		$response = $this->dcaPlanController->actionGetDcaPlanProjection($request, 1);

		self::assertInstanceOf(JsonResponse::class, $response);
		self::assertSame(10, $capturedHorizon);
		self::assertTrue($capturedWithCurrent);
	}

	private function makeCreateDto(?int $assetId = null, ?int $groupId = null, ?int $strategyId = null): DcaPlanCreateDto
	{
		return new DcaPlanCreateDto(
			targetType: DcaPlanTargetTypeEnum::Portfolio,
			assetId: $assetId,
			groupId: $groupId,
			strategyId: $strategyId,
			amount: new Decimal('100'),
			currencyId: 1,
			intervalMonths: 1,
			startDate: new DateTimeImmutable('2024-01-01'),
			endDate: null,
		);
	}

	private function makeUpdateDto(?int $assetId = null, ?int $groupId = null, ?int $strategyId = null): DcaPlanUpdateDto
	{
		return new DcaPlanUpdateDto(
			targetType: DcaPlanTargetTypeEnum::Portfolio,
			assetId: $assetId,
			groupId: $groupId,
			strategyId: $strategyId,
			amount: new Decimal('100'),
			currencyId: 1,
			intervalMonths: 1,
			startDate: new DateTimeImmutable('2024-01-01'),
			endDate: null,
		);
	}
}
