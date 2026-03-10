<?php

declare(strict_types=1);

namespace FinGather\Tests\Controller;

use ArrayIterator;
use FinGather\Controller\PriceAlertController;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\PriceAlert;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\PriceAlertProvider;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Tests\Fixtures\Model\Entity\PriceAlertFixture;
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
#[UsesClass(Ticker::class)]
#[UsesClass(User::class)]
#[UsesClass(NotFoundResponse::class)]
#[UsesClass(OkResponse::class)]
final class PriceAlertControllerTest extends TestCase
{
	private PriceAlertProvider&Stub $priceAlertProvider;

	private PortfolioProvider&Stub $portfolioProvider;

	private TickerProvider&Stub $tickerProvider;

	private RequestServiceInterface&Stub $requestService;

	private PriceAlertController $priceAlertController;

	protected function setUp(): void
	{
		$this->priceAlertProvider = $this::createStub(PriceAlertProvider::class);
		$this->portfolioProvider = $this::createStub(PortfolioProvider::class);
		$this->tickerProvider = $this::createStub(TickerProvider::class);
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
}
