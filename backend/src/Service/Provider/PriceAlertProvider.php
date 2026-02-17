<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\AlertConditionEnum;
use FinGather\Model\Entity\Enum\AlertRecurrenceEnum;
use FinGather\Model\Entity\Enum\PriceAlertTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\PriceAlert;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\PriceAlertRepository;
use Iterator;

class PriceAlertProvider
{
	public function __construct(private readonly PriceAlertRepository $priceAlertRepository)
	{
	}

	/** @return Iterator<PriceAlert> */
	public function getPriceAlerts(User $user): Iterator
	{
		return $this->priceAlertRepository->findPriceAlerts($user->id);
	}

	public function getPriceAlert(int $priceAlertId, User $user): ?PriceAlert
	{
		return $this->priceAlertRepository->findPriceAlert($priceAlertId, $user->id);
	}

	/** @return Iterator<PriceAlert> */
	public function getActivePriceAlerts(): Iterator
	{
		return $this->priceAlertRepository->findActivePriceAlerts();
	}

	public function createPriceAlert(
		User $user,
		PriceAlertTypeEnum $type,
		AlertConditionEnum $condition,
		string $targetValue,
		AlertRecurrenceEnum $recurrence,
		int $cooldownHours,
		?Portfolio $portfolio = null,
		?Ticker $ticker = null,
	): PriceAlert {
		$priceAlert = new PriceAlert(
			user: $user,
			portfolio: $portfolio,
			ticker: $ticker,
			type: $type,
			condition: $condition,
			targetValue: new Decimal($targetValue),
			recurrence: $recurrence,
			cooldownHours: $cooldownHours,
			isActive: true,
			lastTriggeredAt: null,
			createdAt: new DateTimeImmutable(),
		);
		$this->priceAlertRepository->persist($priceAlert);

		return $priceAlert;
	}

	public function updatePriceAlert(
		PriceAlert $priceAlert,
		PriceAlertTypeEnum $type,
		AlertConditionEnum $condition,
		string $targetValue,
		AlertRecurrenceEnum $recurrence,
		int $cooldownHours,
		bool $isActive,
		?Portfolio $portfolio = null,
		?Ticker $ticker = null,
	): PriceAlert {
		$priceAlert->type = $type;
		$priceAlert->condition = $condition;
		$priceAlert->targetValue = new Decimal($targetValue);
		$priceAlert->recurrence = $recurrence;
		$priceAlert->cooldownHours = $cooldownHours;
		$priceAlert->isActive = $isActive;
		$priceAlert->portfolio = $portfolio;
		$priceAlert->ticker = $ticker;
		$this->priceAlertRepository->persist($priceAlert);

		return $priceAlert;
	}

	public function deletePriceAlert(PriceAlert $priceAlert): void
	{
		$this->priceAlertRepository->delete($priceAlert);
	}
}
