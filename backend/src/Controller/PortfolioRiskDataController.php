<?php

declare(strict_types=1);

namespace FinGather\Controller;

use DateTimeImmutable;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Dto\Enum\SamplingFrequencyEnum;
use FinGather\Dto\PortfolioRiskDataDto;
use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Provider\RiskDataProviderInterface;
use FinGather\Service\Provider\TickerProviderInterface;
use FinGather\Service\Request\RequestServiceInterface;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class PortfolioRiskDataController
{
	public function __construct(
		private RiskDataProviderInterface $riskDataProvider,
		private PortfolioProviderInterface $portfolioProvider,
		private TickerProviderInterface $tickerProvider,
		private RequestServiceInterface $requestService,
	) {
	}

	#[RouteGet(Routes::PortfolioRiskData->value)]
	public function actionGetPortfolioRiskData(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		/**
		 * @var array{
		 *     range: value-of<RangeEnum>,
		 *     samplingFrequency?: value-of<SamplingFrequencyEnum>|null,
		 *     benchmarkTickerId?: string|null,
		 *     customRangeFrom?: string|null,
		 *     customRangeTo?: string|null,
		 * } $queryParams
		 */
		$queryParams = $request->getQueryParams();

		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$range = RangeEnum::from($queryParams['range']);
		$samplingFrequency = ($queryParams['samplingFrequency'] ?? null) !== null
			? SamplingFrequencyEnum::from($queryParams['samplingFrequency'])
			: SamplingFrequencyEnum::Daily;

		$benchmarkTickerId = ($queryParams['benchmarkTickerId'] ?? null) !== null ? (int) $queryParams['benchmarkTickerId'] : null;
		$benchmarkTicker = $benchmarkTickerId !== null ? $this->tickerProvider->getTicker($benchmarkTickerId) : null;

		$customRangeFrom = ($queryParams['customRangeFrom'] ?? null) !== null
			? new DateTimeImmutable($queryParams['customRangeFrom'])
			: null;
		$customRangeTo = ($queryParams['customRangeTo'] ?? null) !== null
			? new DateTimeImmutable($queryParams['customRangeTo'])
			: null;

		$riskData = $this->riskDataProvider->getRiskData(
			user: $user,
			portfolio: $portfolio,
			range: $range,
			benchmarkTicker: $benchmarkTicker,
			customRangeFrom: $customRangeFrom,
			customRangeTo: $customRangeTo,
			samplingFrequency: $samplingFrequency,
		);

		return new JsonResponse(PortfolioRiskDataDto::fromRiskDataDto($riskData));
	}
}
