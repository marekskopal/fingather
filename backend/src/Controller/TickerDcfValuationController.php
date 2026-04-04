<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\TickerDcfValuationDto;
use FinGather\Response\ErrorResponse;
use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\DataCalculator\Dcf\DcfCalculationException;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfAssumptions;
use FinGather\Service\Provider\TickerDcfValuationProviderInterface;
use FinGather\Service\Provider\TickerProviderInterface;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class TickerDcfValuationController
{
	public function __construct(
		private TickerDcfValuationProviderInterface $tickerDcfValuationProvider,
		private TickerProviderInterface $tickerProvider,
	) {
	}

	#[RouteGet(Routes::TickerDcfValuation->value)]
	public function actionGetTickerDcfValuation(ServerRequestInterface $request, int $tickerId): ResponseInterface
	{
		if ($tickerId < 1) {
			return new NotFoundResponse('Ticker id is required.');
		}

		$ticker = $this->tickerProvider->getTicker($tickerId);
		if ($ticker === null) {
			return new NotFoundResponse('Ticker with id "' . $tickerId . '" was not found.');
		}

		/**
		 * @var array{wacc?: string, terminalGrowthRate?: string, projectionYears?: string,
		 *     growthRate?: string, fcfMargin?: string} $queryParams
		 */
		$queryParams = $request->getQueryParams();

		$assumptions = DcfAssumptions::default()->with(
			wacc: isset($queryParams['wacc']) ? (float) $queryParams['wacc'] : null,
			terminalGrowthRate: isset($queryParams['terminalGrowthRate']) ? (float) $queryParams['terminalGrowthRate'] : null,
			projectionYears: isset($queryParams['projectionYears']) ? (int) $queryParams['projectionYears'] : null,
			growthRateOverride: isset($queryParams['growthRate']) ? (float) $queryParams['growthRate'] : null,
			fcfMarginOverride: isset($queryParams['fcfMargin']) ? (float) $queryParams['fcfMargin'] : null,
		);

		$validationError = $this->validateAssumptions($assumptions);
		if ($validationError !== null) {
			return new ErrorResponse($validationError, 400);
		}

		try {
			$view = $this->tickerDcfValuationProvider->getDcfValuationView($ticker, $assumptions);
		} catch (DcfCalculationException $exception) {
			return new ErrorResponse($exception->getMessage(), 422);
		}

		if ($view === null) {
			return new NotFoundResponse('DCF valuation for ticker with id "' . $tickerId . '" was not found.');
		}

		return new JsonResponse(TickerDcfValuationDto::fromView($view));
	}

	private function validateAssumptions(DcfAssumptions $assumptions): ?string
	{
		if ($assumptions->wacc <= 0.01 || $assumptions->wacc >= 0.30) {
			return 'WACC must be between 1% and 30%.';
		}

		if ($assumptions->terminalGrowthRate >= $assumptions->wacc) {
			return 'Terminal growth rate must be lower than WACC.';
		}

		if ($assumptions->projectionYears < 1 || $assumptions->projectionYears > 15) {
			return 'Projection years must be between 1 and 15.';
		}

		return null;
	}
}
