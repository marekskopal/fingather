<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Dto\StrategyRebalancingDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Strategy;
use FinGather\Model\Entity\User;

interface StrategyRebalancingProviderInterface
{
	public function getStrategyRebalancing(
		User $user,
		Portfolio $portfolio,
		Strategy $strategy,
		DateTimeImmutable $dateTime,
		Decimal $cashToInvest,
		?int $cashCurrencyId,
		bool $allowSelling,
	): StrategyRebalancingDto;
}
