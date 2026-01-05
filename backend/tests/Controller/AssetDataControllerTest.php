<?php

declare(strict_types=1);

namespace FinGather\Tests\Controller;

use FinGather\Controller\AssetDataController;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Response\ErrorResponse;
use FinGather\Response\NotFoundResponse;
use FinGather\Service\Provider\AssetDataProvider;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(AssetDataController::class)]
#[UsesClass(Asset::class)]
#[UsesClass(Country::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Group::class)]
#[UsesClass(Market::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(Industry::class)]
#[UsesClass(Sector::class)]
#[UsesClass(User::class)]
#[UsesClass(ErrorResponse::class)]
#[UsesClass(NotFoundResponse::class)]
final class AssetDataControllerTest extends TestCase
{
	private AssetDataController $assetDataController;

	protected function setUp(): void
	{
		$this->assetDataController = new AssetDataController(
			$this::createStub(AssetProvider::class),
			$this::createStub(AssetDataProvider::class),
			$this::createStub(TransactionProvider::class),
			$this::createStub(RequestServiceInterface::class),
		);
	}

	public function testGetAssetDataRangeReturnsNotFoundWhenAssetIdIsLessThanOne(): void
	{
		$response = $this->assetDataController->actionGetAssetDataRange($this::createStub(ServerRequestInterface::class), 0);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testActionGetAssetDataRangeReturnsNotFoundWhenAssetNotFound(): void
	{
		$assetProvider = $this::createStub(AssetProvider::class);
		$assetProvider->method('getAsset')->willReturn(null);

		$assetDataController = new AssetDataController(
			$assetProvider,
			$this::createStub(AssetDataProvider::class),
			$this::createStub(TransactionProvider::class),
			$this::createStub(RequestServiceInterface::class),
		);

		$response = $assetDataController->actionGetAssetDataRange($this::createStub(ServerRequestInterface::class), 1);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testActionGetAssetDataRangeReturnsNotFoundWhenRangeNotFound(): void
	{
		$assetProvider = $this::createStub(AssetProvider::class);
		$assetProvider->method('getAsset')->willReturn(AssetFixture::getAsset());

		$transactionProvider = $this::createStub(TransactionProvider::class);
		$transactionProvider->method('getFirstTransaction')->willReturn(null);

		$assetDataController = new AssetDataController(
			$assetProvider,
			$this::createStub(AssetDataProvider::class),
			$this::createStub(TransactionProvider::class),
			$this::createStub(RequestServiceInterface::class),
		);

		$response = $assetDataController->actionGetAssetDataRange($this::createStub(ServerRequestInterface::class), 1);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testActionGetAssetDataRangeReturnsEmptyJsonWhenNoFirstTransaction(): void
	{
		$assetProvider = $this::createStub(AssetProvider::class);
		$assetProvider->method('getAsset')->willReturn(AssetFixture::getAsset());

		$transactionProvider = $this::createStub(TransactionProvider::class);
		$transactionProvider->method('getFirstTransaction')->willReturn(null);

		$request = new ServerRequest(method: 'GET', uri: '/api/asset-data-range/1', queryParams: ['range' => RangeEnum::OneYear->value]);

		$assetDataController = new AssetDataController(
			$assetProvider,
			$this::createStub(AssetDataProvider::class),
			$transactionProvider,
			$this::createStub(RequestServiceInterface::class),
		);

		$response = $assetDataController->actionGetAssetDataRange($request, 1);

		self::assertInstanceOf(JsonResponse::class, $response);
		self::assertEquals([], $response->getPayload());
	}

	public function testActionGetAssetDataRangeReturnsAssetDataWhenValidRequest(): void
	{
		$request = new ServerRequest(method: 'GET', uri: '/api/asset-data-range/1', queryParams: ['range' => RangeEnum::OneYear->value]);

		$response = $this->assetDataController->actionGetAssetDataRange($request, 1);

		self::assertInstanceOf(JsonResponse::class, $response);
	}
}
