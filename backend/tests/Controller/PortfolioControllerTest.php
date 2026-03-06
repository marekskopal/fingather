<?php

declare(strict_types=1);

namespace FinGather\Tests\Controller;

use ArrayIterator;
use FinGather\Controller\PortfolioController;
use FinGather\Dto\PortfolioCreateDto;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Tests\Fixtures\Model\Entity\CurrencyFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(PortfolioController::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(User::class)]
#[UsesClass(NotFoundResponse::class)]
#[UsesClass(OkResponse::class)]
final class PortfolioControllerTest extends TestCase
{
	private PortfolioProvider&Stub $portfolioProvider;

	private CurrencyProvider&Stub $currencyProvider;

	private RequestServiceInterface&Stub $requestService;

	private PortfolioController $portfolioController;

	protected function setUp(): void
	{
		$this->portfolioProvider = $this::createStub(PortfolioProvider::class);
		$this->currencyProvider = $this::createStub(CurrencyProvider::class);
		$this->requestService = $this::createStub(RequestServiceInterface::class);
		$this->requestService->method('getUser')->willReturn(UserFixture::getUser());

		$this->portfolioController = new PortfolioController($this->portfolioProvider, $this->currencyProvider, $this->requestService);
	}

	public function testGetPortfoliosReturnsJsonResponse(): void
	{
		$this->portfolioProvider->method('getPortfolios')->willReturn(new ArrayIterator([]));

		$response = $this->portfolioController->actionGetPortfolios($this::createStub(ServerRequestInterface::class));

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testGetPortfolioInvalidIdReturnsNotFound(int $portfolioId): void
	{
		$response = $this->portfolioController->actionGetPortfolio(
			$this::createStub(ServerRequestInterface::class),
			$portfolioId,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testGetPortfolioNotFoundReturnsNotFound(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(null);

		$response = $this->portfolioController->actionGetPortfolio(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testGetPortfolioReturnsJsonResponse(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());

		$response = $this->portfolioController->actionGetPortfolio(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	public function testGetDefaultPortfolioReturnsJsonResponse(): void
	{
		$this->portfolioProvider->method('getDefaultPortfolio')->willReturn(PortfolioFixture::getPortfolio());

		$response = $this->portfolioController->actionGetDefaultPortfolio(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	public function testPostPortfolioCurrencyNotFoundReturnsNotFound(): void
	{
		$this->currencyProvider->method('getCurrency')->willReturn(null);
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new PortfolioCreateDto(name: 'Test', currencyId: 1, isDefault: false),
		);

		$response = $this->portfolioController->actionPostPortfolio(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPostPortfolioReturnsJsonResponse(): void
	{
		$this->currencyProvider->method('getCurrency')->willReturn(CurrencyFixture::getCurrency());
		$this->portfolioProvider->method('createPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new PortfolioCreateDto(name: 'Test', currencyId: 1, isDefault: false),
		);

		$response = $this->portfolioController->actionPostPortfolio(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testPutPortfolioInvalidIdReturnsNotFound(int $portfolioId): void
	{
		$response = $this->portfolioController->actionPutPortfolio(
			$this::createStub(ServerRequestInterface::class),
			$portfolioId,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPutPortfolioNotFoundReturnsNotFound(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(null);

		$response = $this->portfolioController->actionPutPortfolio(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testDeletePortfolioInvalidIdReturnsNotFound(int $portfolioId): void
	{
		$response = $this->portfolioController->actionDeletePortfolio(
			$this::createStub(ServerRequestInterface::class),
			$portfolioId,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testDeletePortfolioNotFoundReturnsNotFound(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(null);

		$response = $this->portfolioController->actionDeletePortfolio(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testDeletePortfolioReturnsOkResponse(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());

		$response = $this->portfolioController->actionDeletePortfolio(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(OkResponse::class, $response);
	}
}
