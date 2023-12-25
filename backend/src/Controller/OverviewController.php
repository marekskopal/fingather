<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Service\DataCalculator\OverviewDataCalculator;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OverviewController
{
	public function __construct(
		private readonly OverviewDataCalculator $overviewDataCalculator,
		private readonly RequestService $requestService
	) {
	}

	public function actionGetYearOverview(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		return new JsonResponse($this->overviewDataCalculator->yearCalculate($user));
	}
}
