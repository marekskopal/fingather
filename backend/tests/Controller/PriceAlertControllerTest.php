<?php

declare(strict_types=1);

namespace FinGather\Tests\Controller;

use ArrayIterator;
use FinGather\Controller\PriceAlertController;
use FinGather\Dto\PriceAlertCreateDto;
use FinGather\Dto\PriceAlertDto;
use FinGather\Dto\PriceAlertUpdateDto;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\AlertConditionEnum;
use FinGather\Model\Entity\Enum\AlertRecurrenceEnum;
use FinGather\Model\Entity\Enum\PriceAlertTypeEnum;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\PriceAlert;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Provider\PriceAlertProviderInterface;
use FinGather\Service\Provider\TickerProviderInterface;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\PriceAlertFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(PriceAlertController::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(PriceAlert::class)]
#[UsesClass(PriceAlertCreateDto::class)]
#[UsesClass(PriceAlertUpdateDto::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(User::class)]
#[UsesClass(NotFoundResponse::class)]
#[UsesClass(OkResponse::class)]
#[UsesClass(Country::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Industry::class)]
#[UsesClass(Market::class)]
#[UsesClass(Sector::class)]
#[UsesClass(PriceAlertDto::class)]
final class PriceAlertControllerTest extends TestCase
{
	private PriceAlertProviderInterface&Stub $priceAlertProvider;

	private PortfolioProviderInterface&Stub $portfolioProvider;

	private TickerProviderInterface&Stub $tickerProvider;

	private RequestServiceInterface&Stub $requestService;

	private PriceAlertController $priceAlertController;

	protected function setUp(): void
	{
		$this->priceAlertProvider = $this::createStub(PriceAlertProviderInterface::class);
		$this->portfolioProvider = $this::createStub(PortfolioProviderInterface::class);
		$this->tickerProvider = $this::createStub(TickerProviderInterface::class);
		$this->requestService = $this::createStub(RequestServiceInterface::class);
		$this->requestService->method('getUser')->willReturn(UserFixture::getUser());

		$this->priceAlertController = new PriceAlertController(
			$this->priceAlertProvider,
			$this->portfolioProvider,
			$this->tickerProvider,
			$this->requestService,
		);
	}

	public function testGetPriceAlertsReturnsJsonResponse(): void
	{
		$this->priceAlertProvider->method('getPriceAlerts')->willReturn(new ArrayIterator([]));

		$response = $this->priceAlertController->actionGetPriceAlerts(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testGetPriceAlertInvalidIdReturnsNotFound(int $priceAlertId): void
	{
		$response = $this->priceAlertController->actionGetPriceAlert(
			$this::createStub(ServerRequestInterface::class),
			$priceAlertId,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testGetPriceAlertNotFoundReturnsNotFound(): void
	{
		$this->priceAlertProvider->method('getPriceAlert')->willReturn(null);

		$response = $this->priceAlertController->actionGetPriceAlert(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testGetPriceAlertReturnsJsonResponse(): void
	{
		$priceAlert = PriceAlertFixture::getPriceAlert();
		$this->priceAlertProvider->method('getPriceAlert')->willReturn($priceAlert);

		$response = $this->priceAlertController->actionGetPriceAlert(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testPutPriceAlertInvalidIdReturnsNotFound(int $priceAlertId): void
	{
		$response = $this->priceAlertController->actionPutPriceAlert(
			$this::createStub(ServerRequestInterface::class),
			$priceAlertId,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPutPriceAlertNotFoundReturnsNotFound(): void
	{
		$this->priceAlertProvider->method('getPriceAlert')->willReturn(null);

		$response = $this->priceAlertController->actionPutPriceAlert(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testDeletePriceAlertInvalidIdReturnsNotFound(int $priceAlertId): void
	{
		$response = $this->priceAlertController->actionDeletePriceAlert(
			$this::createStub(ServerRequestInterface::class),
			$priceAlertId,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testDeletePriceAlertNotFoundReturnsNotFound(): void
	{
		$this->priceAlertProvider->method('getPriceAlert')->willReturn(null);

		$response = $this->priceAlertController->actionDeletePriceAlert(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testDeletePriceAlertReturnsOkResponse(): void
	{
		$priceAlert = PriceAlertFixture::getPriceAlert();
		$this->priceAlertProvider->method('getPriceAlert')->willReturn($priceAlert);

		$response = $this->priceAlertController->actionDeletePriceAlert(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(OkResponse::class, $response);
	}

	// --- actionPostPriceAlert ---

	public function testPostPriceAlertPortfolioNotFoundReturnsNotFound(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeCreateDto(portfolioId: 99));
		$this->portfolioProvider->method('getPortfolio')->willReturn(null);

		$response = $this->priceAlertController->actionPostPriceAlert($this::createStub(ServerRequestInterface::class));

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPostPriceAlertTickerNotFoundReturnsNotFound(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeCreateDto(tickerId: 99));
		$this->tickerProvider->method('getTicker')->willReturn(null);

		$response = $this->priceAlertController->actionPostPriceAlert($this::createStub(ServerRequestInterface::class));

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPostPriceAlertReturnsJsonResponseOnSuccess(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			$this->makeCreateDto(portfolioId: 1, tickerId: 1),
		);
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->tickerProvider->method('getTicker')->willReturn(TickerFixture::getTicker());
		$this->priceAlertProvider->method('createPriceAlert')->willReturn(PriceAlertFixture::getPriceAlert());

		$response = $this->priceAlertController->actionPostPriceAlert($this::createStub(ServerRequestInterface::class));

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	// --- actionPutPriceAlert ---

	public function testPutPriceAlertPortfolioNotFoundReturnsNotFound(): void
	{
		$this->priceAlertProvider->method('getPriceAlert')->willReturn(PriceAlertFixture::getPriceAlert());
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeUpdateDto(portfolioId: 99));
		$this->portfolioProvider->method('getPortfolio')->willReturn(null);

		$response = $this->priceAlertController->actionPutPriceAlert(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPutPriceAlertTickerNotFoundReturnsNotFound(): void
	{
		$this->priceAlertProvider->method('getPriceAlert')->willReturn(PriceAlertFixture::getPriceAlert());
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeUpdateDto(tickerId: 99));
		$this->tickerProvider->method('getTicker')->willReturn(null);

		$response = $this->priceAlertController->actionPutPriceAlert(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPutPriceAlertReturnsJsonResponseOnSuccess(): void
	{
		$priceAlert = PriceAlertFixture::getPriceAlert();
		$this->priceAlertProvider->method('getPriceAlert')->willReturn($priceAlert);
		$this->requestService->method('getRequestBodyDto')->willReturn(
			$this->makeUpdateDto(portfolioId: 1, tickerId: 1),
		);
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->tickerProvider->method('getTicker')->willReturn(TickerFixture::getTicker());
		$this->priceAlertProvider->method('updatePriceAlert')->willReturn($priceAlert);

		$response = $this->priceAlertController->actionPutPriceAlert(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	private function makeCreateDto(?int $portfolioId = null, ?int $tickerId = null): PriceAlertCreateDto
	{
		return new PriceAlertCreateDto(
			type: PriceAlertTypeEnum::Price,
			condition: AlertConditionEnum::Above,
			targetValue: '200',
			recurrence: AlertRecurrenceEnum::OneTime,
			cooldownHours: 24,
			portfolioId: $portfolioId,
			tickerId: $tickerId,
		);
	}

	private function makeUpdateDto(?int $portfolioId = null, ?int $tickerId = null): PriceAlertUpdateDto
	{
		return new PriceAlertUpdateDto(
			type: PriceAlertTypeEnum::Price,
			condition: AlertConditionEnum::Above,
			targetValue: '250',
			recurrence: AlertRecurrenceEnum::OneTime,
			cooldownHours: 24,
			portfolioId: $portfolioId,
			tickerId: $tickerId,
			isActive: true,
		);
	}
}
