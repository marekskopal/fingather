<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\DcaPlanCreateDto;
use FinGather\Dto\DcaPlanDto;
use FinGather\Dto\DcaPlanUpdateDto;
use FinGather\Model\Entity\DcaPlan;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\DcaPlanProviderInterface;
use FinGather\Service\Provider\GroupProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\StrategyProvider;
use FinGather\Service\Request\RequestServiceInterface;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteDelete;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use MarekSkopal\Router\Attribute\RoutePut;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class DcaPlanController
{
	public function __construct(
		private DcaPlanProviderInterface $dcaPlanProvider,
		private PortfolioProvider $portfolioProvider,
		private AssetProvider $assetProvider,
		private GroupProvider $groupProvider,
		private StrategyProvider $strategyProvider,
		private CurrencyProvider $currencyProvider,
		private RequestServiceInterface $requestService,
	) {
	}

	#[RouteGet(Routes::DcaPlans->value)]
	public function actionGetDcaPlans(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$dcaPlans = array_map(
			fn (DcaPlan $dcaPlan): DcaPlanDto => DcaPlanDto::fromEntity($dcaPlan, $this->dcaPlanProvider->getReturnRate($dcaPlan)),
			iterator_to_array($this->dcaPlanProvider->getDcaPlans($user, $portfolio), false),
		);

		return new JsonResponse($dcaPlans);
	}

	#[RouteGet(Routes::DcaPlan->value)]
	public function actionGetDcaPlan(ServerRequestInterface $request, int $dcaPlanId): ResponseInterface
	{
		if ($dcaPlanId < 1) {
			return new NotFoundResponse('DCA plan id is required.');
		}

		$user = $this->requestService->getUser($request);

		$dcaPlan = $this->dcaPlanProvider->getDcaPlan(dcaPlanId: $dcaPlanId, user: $user);
		if ($dcaPlan === null) {
			return new NotFoundResponse('DCA plan with id "' . $dcaPlanId . '" was not found.');
		}

		return new JsonResponse(DcaPlanDto::fromEntity($dcaPlan, $this->dcaPlanProvider->getReturnRate($dcaPlan)));
	}

	#[RoutePost(Routes::DcaPlans->value)]
	public function actionPostDcaPlan(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$user = $this->requestService->getUser($request);
		$dto = $this->requestService->getRequestBodyDto($request, DcaPlanCreateDto::class);

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$asset = null;
		if ($dto->assetId !== null) {
			$asset = $this->assetProvider->getAsset($user, $dto->assetId);
			if ($asset === null) {
				return new NotFoundResponse('Asset with id "' . $dto->assetId . '" was not found.');
			}
		}

		$group = null;
		if ($dto->groupId !== null) {
			$group = $this->groupProvider->getGroup($user, $dto->groupId);
			if ($group === null) {
				return new NotFoundResponse('Group with id "' . $dto->groupId . '" was not found.');
			}
		}

		$strategy = null;
		if ($dto->strategyId !== null) {
			$strategy = $this->strategyProvider->getStrategy($user, $dto->strategyId);
			if ($strategy === null) {
				return new NotFoundResponse('Strategy with id "' . $dto->strategyId . '" was not found.');
			}
		}

		$currency = $this->currencyProvider->getCurrency($dto->currencyId);
		if ($currency === null) {
			return new NotFoundResponse('Currency with id "' . $dto->currencyId . '" was not found.');
		}

		$dcaPlan = $this->dcaPlanProvider->createDcaPlan(
			user: $user,
			targetType: $dto->targetType,
			portfolio: $portfolio,
			asset: $asset,
			group: $group,
			strategy: $strategy,
			amount: $dto->amount,
			currency: $currency,
			intervalMonths: $dto->intervalMonths,
			startDate: $dto->startDate,
			endDate: $dto->endDate,
		);

		return new JsonResponse(DcaPlanDto::fromEntity($dcaPlan, $this->dcaPlanProvider->getReturnRate($dcaPlan)));
	}

	#[RoutePut(Routes::DcaPlan->value)]
	public function actionPutDcaPlan(ServerRequestInterface $request, int $dcaPlanId): ResponseInterface
	{
		if ($dcaPlanId < 1) {
			return new NotFoundResponse('DCA plan id is required.');
		}

		$user = $this->requestService->getUser($request);

		$dcaPlan = $this->dcaPlanProvider->getDcaPlan(dcaPlanId: $dcaPlanId, user: $user);
		if ($dcaPlan === null) {
			return new NotFoundResponse('DCA plan with id "' . $dcaPlanId . '" was not found.');
		}

		$dto = $this->requestService->getRequestBodyDto($request, DcaPlanUpdateDto::class);

		$portfolio = $dcaPlan->portfolio;

		$asset = null;
		if ($dto->assetId !== null) {
			$asset = $this->assetProvider->getAsset($user, $dto->assetId);
			if ($asset === null) {
				return new NotFoundResponse('Asset with id "' . $dto->assetId . '" was not found.');
			}
		}

		$group = null;
		if ($dto->groupId !== null) {
			$group = $this->groupProvider->getGroup($user, $dto->groupId);
			if ($group === null) {
				return new NotFoundResponse('Group with id "' . $dto->groupId . '" was not found.');
			}
		}

		$strategy = null;
		if ($dto->strategyId !== null) {
			$strategy = $this->strategyProvider->getStrategy($user, $dto->strategyId);
			if ($strategy === null) {
				return new NotFoundResponse('Strategy with id "' . $dto->strategyId . '" was not found.');
			}
		}

		$currency = $this->currencyProvider->getCurrency($dto->currencyId);
		if ($currency === null) {
			return new NotFoundResponse('Currency with id "' . $dto->currencyId . '" was not found.');
		}

		$dcaPlan = $this->dcaPlanProvider->updateDcaPlan(
			dcaPlan: $dcaPlan,
			targetType: $dto->targetType,
			portfolio: $portfolio,
			asset: $asset,
			group: $group,
			strategy: $strategy,
			amount: $dto->amount,
			currency: $currency,
			intervalMonths: $dto->intervalMonths,
			startDate: $dto->startDate,
			endDate: $dto->endDate,
		);

		return new JsonResponse(DcaPlanDto::fromEntity($dcaPlan, $this->dcaPlanProvider->getReturnRate($dcaPlan)));
	}

	#[RouteDelete(Routes::DcaPlan->value)]
	public function actionDeleteDcaPlan(ServerRequestInterface $request, int $dcaPlanId): ResponseInterface
	{
		if ($dcaPlanId < 1) {
			return new NotFoundResponse('DCA plan id is required.');
		}

		$dcaPlan = $this->dcaPlanProvider->getDcaPlan(
			dcaPlanId: $dcaPlanId,
			user: $this->requestService->getUser($request),
		);
		if ($dcaPlan === null) {
			return new NotFoundResponse('DCA plan with id "' . $dcaPlanId . '" was not found.');
		}

		$this->dcaPlanProvider->deleteDcaPlan($dcaPlan);

		return new OkResponse();
	}

	#[RouteGet(Routes::DcaPlanProjection->value)]
	public function actionGetDcaPlanProjection(ServerRequestInterface $request, int $dcaPlanId): ResponseInterface
	{
		if ($dcaPlanId < 1) {
			return new NotFoundResponse('DCA plan id is required.');
		}

		$user = $this->requestService->getUser($request);

		$dcaPlan = $this->dcaPlanProvider->getDcaPlan(dcaPlanId: $dcaPlanId, user: $user);
		if ($dcaPlan === null) {
			return new NotFoundResponse('DCA plan with id "' . $dcaPlanId . '" was not found.');
		}

		/** @var array{horizonYears?: string, withCurrentValue?: string} $queryParams */
		$queryParams = $request->getQueryParams();
		$horizonYears = isset($queryParams['horizonYears']) ? (int) $queryParams['horizonYears'] : 10;
		$withCurrentValue = !isset($queryParams['withCurrentValue']) || $queryParams['withCurrentValue'] !== 'false';

		$projection = $this->dcaPlanProvider->getProjection($dcaPlan, $horizonYears, $withCurrentValue);

		return new JsonResponse($projection);
	}
}
