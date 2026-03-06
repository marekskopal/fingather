<?php

declare(strict_types=1);

namespace FinGather\Tests\Controller;

use ArrayIterator;
use FinGather\Controller\TransactionController;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\BrokerProvider;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\DataProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TransactionFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(TransactionController::class)]
#[UsesClass(Asset::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(Transaction::class)]
#[UsesClass(User::class)]
#[UsesClass(NotFoundResponse::class)]
#[UsesClass(OkResponse::class)]
final class TransactionControllerTest extends TestCase
{
	private TransactionProvider&Stub $transactionProvider;

	private AssetProvider&Stub $assetProvider;

	private PortfolioProvider&Stub $portfolioProvider;

	private RequestServiceInterface&Stub $requestService;

	private TransactionController $transactionController;

	protected function setUp(): void
	{
		$this->transactionProvider = $this::createStub(TransactionProvider::class);
		$this->assetProvider = $this::createStub(AssetProvider::class);
		$this->portfolioProvider = $this::createStub(PortfolioProvider::class);
		$this->requestService = $this::createStub(RequestServiceInterface::class);
		$this->requestService->method('getUser')->willReturn(UserFixture::getUser());

		$this->transactionController = new TransactionController(
			$this->transactionProvider,
			$this->assetProvider,
			$this::createStub(BrokerProvider::class),
			$this::createStub(CurrencyProvider::class),
			$this::createStub(DataProvider::class),
			$this->portfolioProvider,
			$this->requestService,
		);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testGetTransactionsInvalidPortfolioIdReturnsNotFound(int $portfolioId): void
	{
		$request = $this::createStub(ServerRequestInterface::class);
		$request->method('getQueryParams')->willReturn([]);

		$response = $this->transactionController->actionGetTransactions($request, $portfolioId);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testGetTransactionsPortfolioNotFoundReturnsNotFound(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(null);
		$request = $this::createStub(ServerRequestInterface::class);
		$request->method('getQueryParams')->willReturn([]);

		$response = $this->transactionController->actionGetTransactions($request, 1);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testGetTransactionsReturnsJsonResponse(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->transactionProvider->method('getTransactions')->willReturn(new ArrayIterator([]));
		$this->transactionProvider->method('countTransactions')->willReturn(0);
		$request = $this::createStub(ServerRequestInterface::class);
		$request->method('getQueryParams')->willReturn([]);

		$response = $this->transactionController->actionGetTransactions($request, 1);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testGetTransactionInvalidIdReturnsNotFound(int $transactionId): void
	{
		$response = $this->transactionController->actionGetTransaction(
			$this::createStub(ServerRequestInterface::class),
			$transactionId,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testGetTransactionNotFoundReturnsNotFound(): void
	{
		$this->transactionProvider->method('getTransaction')->willReturn(null);

		$response = $this->transactionController->actionGetTransaction(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testGetTransactionReturnsJsonResponse(): void
	{
		$this->transactionProvider->method('getTransaction')->willReturn(TransactionFixture::getTransaction());

		$response = $this->transactionController->actionGetTransaction(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testUpdateTransactionInvalidIdReturnsNotFound(int $transactionId): void
	{
		$response = $this->transactionController->actionUpdateTransaction(
			$this::createStub(ServerRequestInterface::class),
			$transactionId,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testUpdateTransactionNotFoundReturnsNotFound(): void
	{
		$this->transactionProvider->method('getTransaction')->willReturn(null);

		$response = $this->transactionController->actionUpdateTransaction(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testDeleteTransactionInvalidIdReturnsNotFound(int $transactionId): void
	{
		$response = $this->transactionController->actionDeleteTransaction(
			$this::createStub(ServerRequestInterface::class),
			$transactionId,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testDeleteTransactionNotFoundReturnsNotFound(): void
	{
		$this->transactionProvider->method('getTransaction')->willReturn(null);

		$response = $this->transactionController->actionDeleteTransaction(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testDeleteTransactionReturnsOkResponse(): void
	{
		$this->transactionProvider->method('getTransaction')->willReturn(TransactionFixture::getTransaction());

		$response = $this->transactionController->actionDeleteTransaction(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(OkResponse::class, $response);
	}
}
