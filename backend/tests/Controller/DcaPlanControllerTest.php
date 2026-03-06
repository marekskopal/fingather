<?php

declare(strict_types=1);

namespace FinGather\Tests\Controller;

use ArrayIterator;
use FinGather\Controller\DcaPlanController;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\DcaPlan;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Service\DataCalculator\Dto\ReturnRateDto;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\DcaPlanProviderInterface;
use FinGather\Service\Provider\GroupProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\StrategyProvider;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Tests\Fixtures\Model\Entity\DcaPlanFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
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
#[UsesClass(Portfolio::class)]
#[UsesClass(User::class)]
#[UsesClass(NotFoundResponse::class)]
#[UsesClass(OkResponse::class)]
final class DcaPlanControllerTest extends TestCase
{
	private DcaPlanProviderInterface&Stub $dcaPlanProvider;

	private PortfolioProvider&Stub $portfolioProvider;

	private RequestServiceInterface&Stub $requestService;

	private DcaPlanController $dcaPlanController;

	protected function setUp(): void
	{
		$this->dcaPlanProvider = $this::createStub(DcaPlanProviderInterface::class);
		$this->portfolioProvider = $this::createStub(PortfolioProvider::class);
		$this->requestService = $this::createStub(RequestServiceInterface::class);
		$this->requestService->method('getUser')->willReturn(UserFixture::getUser());
		$this->dcaPlanProvider->method('getReturnRate')->willReturn(new ReturnRateDto(annual: 7.0, monthly: 0.58));

		$this->dcaPlanController = new DcaPlanController(
			$this->dcaPlanProvider,
			$this->portfolioProvider,
			$this::createStub(AssetProvider::class),
			$this::createStub(GroupProvider::class),
			$this::createStub(StrategyProvider::class),
			$this::createStub(CurrencyProvider::class),
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
		$this->dcaPlanProvider->method('getDcaPlan')->willReturn(DcaPlanFixture::getDcaPlan());

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
		$this->dcaPlanProvider->method('getDcaPlan')->willReturn(DcaPlanFixture::getDcaPlan());

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
}
