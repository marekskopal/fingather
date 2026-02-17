<?php

declare(strict_types=1);

namespace FinGather\Tests\Controller;

use FinGather\Controller\CountryWithCountryDataController;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Response\ErrorResponse;
use FinGather\Response\NotFoundResponse;
use FinGather\Service\Provider\CountryWithCountryDataProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(CountryWithCountryDataController::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(User::class)]
#[UsesClass(ErrorResponse::class)]
#[UsesClass(NotFoundResponse::class)]
final class CountryWithCountryDataControllerTest extends TestCase
{
	private CountryWithCountryDataProvider&Stub $countryWithCountryDataProvider;

	private PortfolioProvider&Stub $portfolioProvider;

	private RequestServiceInterface&Stub $requestService;

	private CountryWithCountryDataController $countryWithCountryDataController;

	protected function setUp(): void
	{
		$this->countryWithCountryDataProvider = $this::createStub(CountryWithCountryDataProvider::class);
		$this->portfolioProvider = $this::createStub(PortfolioProvider::class);
		$this->requestService = $this::createStub(RequestServiceInterface::class);

		$this->countryWithCountryDataController = new CountryWithCountryDataController(
			$this->countryWithCountryDataProvider,
			$this->portfolioProvider,
			$this->requestService,
		);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testInvalidPortfolioIdReturnsNotFound(int $portfolioId): void
	{
		$request = $this::createStub(ServerRequestInterface::class);

		$response = $this->countryWithCountryDataController->actionGetCountriesWithCountryData($request, $portfolioId);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPortfolioNotFoundReturnsNotFound(): void
	{
		$portfolioId = 1;
		$request = $this::createStub(ServerRequestInterface::class);
		$this->requestService->method('getUser')->willReturn(UserFixture::getUser());
		$this->portfolioProvider->method('getPortfolio')->willReturn(null);

		$response = $this->countryWithCountryDataController->actionGetCountriesWithCountryData($request, $portfolioId);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testGetCountriesWithCountryDataReturnsJsonResponse(): void
	{
		$portfolioId = 1;
		$request = $this::createStub(ServerRequestInterface::class);
		$this->requestService->method('getUser')->willReturn(UserFixture::getUser());
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->countryWithCountryDataProvider->method('getCountriesWithCountryData')->willReturn([]);

		$response = $this->countryWithCountryDataController->actionGetCountriesWithCountryData($request, $portfolioId);

		self::assertInstanceOf(JsonResponse::class, $response);
	}
}
