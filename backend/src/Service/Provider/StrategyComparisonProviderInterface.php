<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Dto\StrategyWithComparisonDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Strategy;
use FinGather\Model\Entity\User;

interface StrategyComparisonProviderInterface
{
	public function getStrategyWithComparison(
		User $user,
		Portfolio $portfolio,
		Strategy $strategy,
		DateTimeImmutable $dateTime,
	): StrategyWithComparisonDto;
}
