<?php

declare(strict_types=1);

namespace FinGather\Service;

use DateTimeImmutable;
use FinGather\Model\Entity\Enum\AlertConditionEnum;
use FinGather\Model\Entity\Enum\AlertRecurrenceEnum;
use FinGather\Model\Entity\Enum\PriceAlertTypeEnum;
use FinGather\Model\Entity\PriceAlert;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\PriceAlertProvider;
use FinGather\Service\Provider\TickerDataProvider;

class PriceAlertChecker
{
	public function __construct(
		private readonly PriceAlertProvider $priceAlertProvider,
		private readonly TickerDataProvider $tickerDataProvider,
		private readonly PortfolioDataProvider $portfolioDataProvider,
		private readonly PortfolioProvider $portfolioProvider,
	) {
	}

	/** @return list<array{alert: PriceAlert, currentValue: string}> */
	public function checkAlerts(): array
	{
		$triggeredAlerts = [];
		$now = new DateTimeImmutable();

		foreach ($this->priceAlertProvider->getActivePriceAlerts() as $alert) {
			if ($this->isCooldownActive($alert, $now)) {
				continue;
			}

			$currentValue = match ($alert->type) {
				PriceAlertTypeEnum::Price => $this->checkPriceAlert($alert, $now),
				PriceAlertTypeEnum::Portfolio => $this->checkPortfolioAlert($alert, $now),
			};

			if ($currentValue === null) {
				continue;
			}

			$triggeredAlerts[] = ['alert' => $alert, 'currentValue' => $currentValue];
		}

		return $triggeredAlerts;
	}

	private function isCooldownActive(PriceAlert $alert, DateTimeImmutable $now): bool
	{
		if ($alert->lastTriggeredAt === null) {
			return false;
		}

		$cooldownSeconds = $alert->cooldownHours * 3600;
		return $now->getTimestamp() - $alert->lastTriggeredAt->getTimestamp() < $cooldownSeconds;
	}

	private function checkPriceAlert(PriceAlert $alert, DateTimeImmutable $now): ?string
	{
		if ($alert->ticker === null) {
			return null;
		}

		$lastClose = $this->tickerDataProvider->getLastTickerDataClose($alert->ticker, $now);
		if ($lastClose === null) {
			return null;
		}

		$isTriggered = match ($alert->condition) {
			AlertConditionEnum::Above => $lastClose->compareTo($alert->targetValue) >= 0,
			AlertConditionEnum::Below => $lastClose->compareTo($alert->targetValue) <= 0,
		};

		if (!$isTriggered) {
			return null;
		}

		return $lastClose->toFixed(2);
	}

	private function checkPortfolioAlert(PriceAlert $alert, DateTimeImmutable $now): ?string
	{
		$portfolio = $alert->portfolio;
		if ($portfolio === null) {
			$portfolio = $this->portfolioProvider->getDefaultPortfolio($alert->user);
		}

		$portfolioData = $this->portfolioDataProvider->getPortfolioData($alert->user, $portfolio, $now);

		$gainPercentage = $portfolioData->gainPercentage;

		$isTriggered = match ($alert->condition) {
			AlertConditionEnum::Above => $gainPercentage >= (float) $alert->targetValue->toString(),
			AlertConditionEnum::Below => $gainPercentage <= (float) $alert->targetValue->toString(),
		};

		if (!$isTriggered) {
			return null;
		}

		return number_format($gainPercentage, 2);
	}

	public function markTriggered(PriceAlert $alert): void
	{
		$alert->lastTriggeredAt = new DateTimeImmutable();

		if ($alert->recurrence === AlertRecurrenceEnum::OneTime) {
			$alert->isActive = false;
		}
	}
}
