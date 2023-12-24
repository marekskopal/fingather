<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\OverviewYearDto;
use FinGather\Dto\PortfolioDataDto;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTimeImmutable;

class OverviewController
{
	public function __construct(
		private readonly TransactionProvider $transactionProvider,
		private readonly PortfolioDataProvider $portfolioDataProvider,
		private readonly RequestService $requestService
	)
	{
	}

	public function actionGetYearOverview(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$firstTransaction = $this->transactionProvider->getFirstTransaction($user);
		if ($firstTransaction === null) {
			return new JsonResponse([]);
		}

		$fromDate = $firstTransaction->getCreated();
		$toDate = new DateTimeImmutable('today');

		$fromDateYear = (int) $fromDate->format('Y');
		$toDateYear = (int) $toDate->format('Y');

		$yearOverviews = [];

		for ($i = $fromDateYear; $i <= $toDateYear; $i++) {
			$yearFromDate = (new DateTimeImmutable('first day of january ' . $i))->setTime(0, 0);
			$yearToDate = (new DateTimeImmutable('last day of december ' . $i))->setTime(0, 0);

			$portfolioDataFromDate = PortfolioDataDto::fromEntity($this->portfolioDataProvider->getPortfolioData($user, $yearFromDate));
			$portfolioDataToDate = PortfolioDataDto::fromEntity($this->portfolioDataProvider->getPortfolioData($user, $yearToDate));

			if ($i === $fromDateYear) {
				$yearOverviews[] = new OverviewYearDto(
					year: $i,
					value: $portfolioDataToDate->value,
					transactionValue: $portfolioDataToDate->transactionValue,
					gain: $portfolioDataToDate->gain,
					gainPercentage: $portfolioDataToDate->gainPercentage,
					dividendGain: $portfolioDataToDate->dividendGain,
					dividendGainPercentage: $portfolioDataToDate->dividendGainPercentage,
					fxImpact: $portfolioDataToDate->fxImpact,
					fxImpactPercentage: $portfolioDataToDate->fxImpactPercentage,
					return: $portfolioDataToDate->return,
					returnPercentage: $portfolioDataToDate->returnPercentage,
					performance: 0.0,
				);
				continue;
			}

			$transactionValue = $portfolioDataToDate->transactionValue->sub($portfolioDataFromDate->transactionValue);
			$gain = $portfolioDataToDate->gain->sub($portfolioDataFromDate->gain);
			$dividendGain = $portfolioDataToDate->dividendGain->sub($portfolioDataFromDate->dividendGain);
			$fxImpact = $portfolioDataToDate->fxImpact->sub($portfolioDataFromDate->fxImpact);
			$return = $portfolioDataToDate->return->sub($portfolioDataFromDate->return);

			$gainPercentage = round($gain->div($portfolioDataFromDate->transactionValue)->mul(100)->toFloat(), 2);
			$dividendGainPercentage = round($dividendGain->div($portfolioDataFromDate->transactionValue)->mul(100)->toFloat(), 2);
			$fxImpactPercentage = round($fxImpact->div($portfolioDataFromDate->transactionValue)->mul(100)->toFloat(), 2);

			$yearOverviews[] = new OverviewYearDto(
				year: $i,
				value: $portfolioDataToDate->value,
				transactionValue: $transactionValue,
				gain: $gain,
				gainPercentage: $gainPercentage,
				dividendGain: $dividendGain,
				dividendGainPercentage: $dividendGainPercentage,
				fxImpact: $fxImpact,
				fxImpactPercentage: $fxImpactPercentage,
				return: $return,
				returnPercentage: round($gainPercentage + $dividendGainPercentage + $fxImpactPercentage, 2),
				performance: 0.0,
			);
		}

		return new JsonResponse($yearOverviews);
	}
}
