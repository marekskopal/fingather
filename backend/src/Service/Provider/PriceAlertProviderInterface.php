<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Enum\AlertConditionEnum;
use FinGather\Model\Entity\Enum\AlertRecurrenceEnum;
use FinGather\Model\Entity\Enum\PriceAlertTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\PriceAlert;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use Iterator;

interface PriceAlertProviderInterface
{
	/** @return Iterator<PriceAlert> */
	public function getPriceAlerts(User $user): Iterator;

	public function getPriceAlert(int $priceAlertId, User $user): ?PriceAlert;

	/** @return Iterator<PriceAlert> */
	public function getActivePriceAlerts(): Iterator;

	public function createPriceAlert(
		User $user,
		PriceAlertTypeEnum $type,
		AlertConditionEnum $condition,
		string $targetValue,
		AlertRecurrenceEnum $recurrence,
		int $cooldownHours,
		?Portfolio $portfolio = null,
		?Ticker $ticker = null,
	): PriceAlert;

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
	): PriceAlert;

	public function deletePriceAlert(PriceAlert $priceAlert): void;
}
