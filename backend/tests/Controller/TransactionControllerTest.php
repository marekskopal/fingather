<?php

declare(strict_types=1);

namespace FinGather\Tests\Controller;

use ArrayIterator;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Controller\TransactionController;
use FinGather\Dto\CountryDto;
use FinGather\Dto\IndustryDto;
use FinGather\Dto\MarketDto;
use FinGather\Dto\SectorDto;
use FinGather\Dto\TickerDto;
use FinGather\Dto\TransactionCreateDto;
use FinGather\Dto\TransactionDto;
use FinGather\Dto\TransactionListDto;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\BrokerProviderInterface;
use FinGather\Service\Provider\CurrencyProviderInterface;
use FinGather\Service\Provider\DataProviderInterface;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Provider\TransactionProviderInterface;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\CurrencyFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TransactionFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use FinGather\Utils\DateTimeUtils;
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
#[UsesClass(TransactionCreateDto::class)]
#[UsesClass(User::class)]
#[UsesClass(NotFoundResponse::class)]
#[UsesClass(OkResponse::class)]
#[UsesClass(IndustryDto::class)]
#[UsesClass(MarketDto::class)]
#[UsesClass(SectorDto::class)]
#[UsesClass(TickerDto::class)]
#[UsesClass(TransactionDto::class)]
#[UsesClass(Country::class)]
#[UsesClass(Group::class)]
#[UsesClass(Industry::class)]
#[UsesClass(Market::class)]
#[UsesClass(Sector::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(DateTimeUtils::class)]
#[UsesClass(CountryDto::class)]
#[UsesClass(TransactionListDto::class)]
final class TransactionControllerTest extends TestCase
{
	private TransactionProviderInterface&Stub $transactionProvider;

	private AssetProviderInterface&Stub $assetProvider;

	private BrokerProviderInterface&Stub $brokerProvider;

	private CurrencyProviderInterface&Stub $currencyProvider;

	private DataProviderInterface&Stub $dataProvider;

	private PortfolioProviderInterface&Stub $portfolioProvider;

	private RequestServiceInterface&Stub $requestService;

	private TransactionController $transactionController;

	protected function setUp(): void
	{
		$this->transactionProvider = $this::createStub(TransactionProviderInterface::class);
		$this->assetProvider = $this::createStub(AssetProviderInterface::class);
		$this->brokerProvider = $this::createStub(BrokerProviderInterface::class);
		$this->currencyProvider = $this::createStub(CurrencyProviderInterface::class);
		$this->dataProvider = $this::createStub(DataProviderInterface::class);
		$this->portfolioProvider = $this::createStub(PortfolioProviderInterface::class);
		$this->requestService = $this::createStub(RequestServiceInterface::class);
		$this->requestService->method('getUser')->willReturn(UserFixture::getUser());

		$this->transactionController = new TransactionController(
			$this->transactionProvider,
			$this->assetProvider,
			$this->brokerProvider,
			$this->currencyProvider,
			$this->dataProvider,
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

	// --- actionCreateTransaction ---

	public function testCreateTransactionInvalidPortfolioIdReturnsNotFound(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeCreateDto());

		$response = $this->transactionController->actionCreateTransaction(
			$this::createStub(ServerRequestInterface::class),
			0,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testCreateTransactionPortfolioNotFoundReturnsNotFound(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeCreateDto());
		$this->portfolioProvider->method('getPortfolio')->willReturn(null);

		$response = $this->transactionController->actionCreateTransaction(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testCreateTransactionAssetNotFoundReturnsNotFound(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeCreateDto());
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->assetProvider->method('getAsset')->willReturn(null);

		$response = $this->transactionController->actionCreateTransaction(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testCreateTransactionCurrencyNotFoundReturnsNotFound(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeCreateDto());
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->assetProvider->method('getAsset')->willReturn(AssetFixture::getAsset());
		$this->currencyProvider->method('getCurrency')->willReturn(null);

		$response = $this->transactionController->actionCreateTransaction(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testCreateTransactionReturnsJsonResponseOnSuccess(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeCreateDto());
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->assetProvider->method('getAsset')->willReturn(AssetFixture::getAsset());
		$this->currencyProvider->method('getCurrency')->willReturn(CurrencyFixture::getCurrency());
		$this->transactionProvider->method('createTransaction')->willReturn(TransactionFixture::getTransaction());

		$response = $this->transactionController->actionCreateTransaction(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	// --- actionUpdateTransaction ---

	public function testUpdateTransactionAssetNotFoundReturnsNotFound(): void
	{
		$this->transactionProvider->method('getTransaction')->willReturn(TransactionFixture::getTransaction());
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeCreateDto());
		$this->assetProvider->method('getAsset')->willReturn(null);

		$response = $this->transactionController->actionUpdateTransaction(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testUpdateTransactionCurrencyNotFoundReturnsNotFound(): void
	{
		$this->transactionProvider->method('getTransaction')->willReturn(TransactionFixture::getTransaction());
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeCreateDto());
		$this->assetProvider->method('getAsset')->willReturn(AssetFixture::getAsset());
		$this->currencyProvider->method('getCurrency')->willReturn(null);

		$response = $this->transactionController->actionUpdateTransaction(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testUpdateTransactionReturnsJsonResponseOnSuccess(): void
	{
		$transaction = TransactionFixture::getTransaction();
		$this->transactionProvider->method('getTransaction')->willReturn($transaction);
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeCreateDto());
		$this->assetProvider->method('getAsset')->willReturn(AssetFixture::getAsset());
		$this->currencyProvider->method('getCurrency')->willReturn(CurrencyFixture::getCurrency());
		$this->transactionProvider->method('updateTransaction')->willReturn($transaction);

		$response = $this->transactionController->actionUpdateTransaction(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	private function makeCreateDto(): TransactionCreateDto
	{
		return new TransactionCreateDto(
			assetId: 1,
			brokerId: null,
			actionType: TransactionActionTypeEnum::Buy,
			actionCreated: new DateTimeImmutable('2024-01-01'),
			units: new Decimal('5'),
			price: new Decimal('100'),
			currencyId: 1,
			tax: new Decimal('0'),
			taxCurrencyId: 1,
			fee: new Decimal('0'),
			feeCurrencyId: 1,
			notes: null,
			importIdentifier: null,
		);
	}
}
