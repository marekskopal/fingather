<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\StrategyItemCreateDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Strategy;
use FinGather\Model\Entity\User;

interface StrategyProviderInterface
{
	/** @return list<Strategy> */
	public function getStrategies(User $user, Portfolio $portfolio): array;

	public function getStrategy(User $user, int $strategyId): ?Strategy;

	public function getDefaultStrategy(User $user, Portfolio $portfolio): ?Strategy;

	/** @param list<StrategyItemCreateDto> $items */
	public function createStrategy(User $user, Portfolio $portfolio, string $name, bool $isDefault, array $items): Strategy;

	/** @param list<StrategyItemCreateDto> $items */
	public function updateStrategy(Strategy $strategy, string $name, bool $isDefault, array $items): Strategy;

	public function deleteStrategy(Strategy $strategy): void;
}
