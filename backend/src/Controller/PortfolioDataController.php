<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\Enum\PortfolioDataRangeEnum;
use FinGather\Dto\PortfolioDataDto;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Service\Request\RequestService;
use FinGather\Utils\DateTimeUtils;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTimeImmutable;

class PortfolioDataController
{
	public function __construct(
		private readonly PortfolioDataProvider $portfolioDataProvider,
		private readonly TransactionProvider $transactionProvider,
		private readonly RequestService $requestService,
	) {
	}

	public function actionGetPortfolioData(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$dateTime = new DateTimeImmutable();

		$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $dateTime);

		return new JsonResponse(PortfolioDataDto::fromEntity($portfolioData));
	}

	public function actionGetPortfolioDataRange(ServerRequestInterface $request): ResponseInterface
	{
		/** @var array{range: value-of<PortfolioDataRangeEnum>} $queryParams */
		$queryParams = $request->getQueryParams();

		$user = $this->requestService->getUser($request);

		$range = PortfolioDataRangeEnum::from($queryParams['range']);

		$firstTransaction = $this->transactionProvider->getFirstTransaction($user);
		if ($firstTransaction === null) {
			return new JsonResponse([]);
		}

		$portfolioData = [];

		foreach (DateTimeUtils::getDatePeriod($range, $firstTransaction->getActionCreated()) as $dateTime) {
			/** @var \DateTimeImmutable $dateTime */
			$portfolioData[] = PortfolioDataDto::fromEntity(
				$this->portfolioDataProvider->getPortfolioData($user, DateTimeImmutable::createFromRegular($dateTime)),
			);
		}

		return new JsonResponse($portfolioData);
	}
}
