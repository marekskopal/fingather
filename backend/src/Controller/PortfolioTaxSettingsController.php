<?php

declare(strict_types=1);

namespace FinGather\Controller;

use Decimal\Decimal;
use FinGather\Dto\PortfolioTaxSettingsDto;
use FinGather\Dto\PortfolioTaxSettingsUpdateDto;
use FinGather\Model\Entity\Enum\CostBasisMethodEnum;
use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;
use FinGather\Response\ErrorResponse;
use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Service\Tax\Jurisdiction\TaxJurisdictionRulesFactory;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePut;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final readonly class PortfolioTaxSettingsController
{
	public function __construct(
		private PortfolioProviderInterface $portfolioProvider,
		private TaxJurisdictionRulesFactory $taxJurisdictionRulesFactory,
		private RequestServiceInterface $requestService,
	) {
	}

	#[RouteGet(Routes::PortfolioTaxSettings->value)]
	public function actionGetPortfolioTaxSettings(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio(
			user: $this->requestService->getUser($request),
			portfolioId: $portfolioId,
		);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$rules = $this->taxJurisdictionRulesFactory->forPortfolio($portfolio);

		return new JsonResponse(PortfolioTaxSettingsDto::fromEntity($portfolio, $rules));
	}

	#[RoutePut(Routes::PortfolioTaxSettings->value)]
	public function actionPutPortfolioTaxSettings(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio(
			user: $this->requestService->getUser($request),
			portfolioId: $portfolioId,
		);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$updateDto = $this->requestService->getRequestBodyDto($request, PortfolioTaxSettingsUpdateDto::class);

		$jurisdiction = TaxJurisdictionEnum::tryFrom($updateDto->taxJurisdiction);
		if ($jurisdiction === null) {
			return new ErrorResponse('Invalid taxJurisdiction value.', status: 400);
		}

		$method = CostBasisMethodEnum::tryFrom($updateDto->costBasisMethod);
		if ($method === null) {
			return new ErrorResponse('Invalid costBasisMethod value.', status: 400);
		}

		$rules = $this->taxJurisdictionRulesFactory->forJurisdiction($jurisdiction);
		if (!in_array($method, $rules->allowedCostBasisMethods(), strict: true)) {
			return new ErrorResponse(
				sprintf(
					'Cost basis method "%s" is not allowed for jurisdiction "%s".',
					$method->value,
					$jurisdiction->value,
				),
				status: 400,
			);
		}

		$estimatedTaxRate = null;
		if ($updateDto->estimatedTaxRate !== null && $updateDto->estimatedTaxRate !== '') {
			try {
				$estimatedTaxRate = new Decimal($updateDto->estimatedTaxRate);
			} catch (Throwable) {
				return new ErrorResponse('Invalid estimatedTaxRate value.', status: 400);
			}

			if ($estimatedTaxRate->isNegative() || $estimatedTaxRate >= new Decimal(1)) {
				return new ErrorResponse('estimatedTaxRate must be in the range [0, 1).', status: 400);
			}
		}

		$updated = $this->portfolioProvider->updateTaxSettings(
			portfolio: $portfolio,
			taxJurisdiction: $jurisdiction,
			costBasisMethod: $method,
			estimatedTaxRate: $estimatedTaxRate,
		);

		return new JsonResponse(PortfolioTaxSettingsDto::fromEntity($updated, $rules));
	}
}
