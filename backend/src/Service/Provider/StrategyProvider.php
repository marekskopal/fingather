<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use ArrayIterator;
use Decimal\Decimal;
use FinGather\Dto\StrategyItemCreateDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Strategy;
use FinGather\Model\Entity\StrategyItem;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Model\Repository\GroupRepository;
use FinGather\Model\Repository\StrategyItemRepository;
use FinGather\Model\Repository\StrategyRepository;
use Iterator;

class StrategyProvider
{
	public function __construct(
		private readonly StrategyRepository $strategyRepository,
		private readonly StrategyItemRepository $strategyItemRepository,
		private readonly AssetRepository $assetRepository,
		private readonly GroupRepository $groupRepository,
	) {
	}

	/** @return Iterator<Strategy> */
	public function getStrategies(User $user, Portfolio $portfolio): Iterator
	{
		return $this->strategyRepository->findStrategies($user->id, $portfolio->id);
	}

	public function getStrategy(User $user, int $strategyId): ?Strategy
	{
		return $this->strategyRepository->findStrategy($user->id, $strategyId);
	}

	public function getDefaultStrategy(User $user, Portfolio $portfolio): ?Strategy
	{
		return $this->strategyRepository->findDefaultStrategy($user->id, $portfolio->id);
	}

	/** @param list<StrategyItemCreateDto> $items */
	public function createStrategy(User $user, Portfolio $portfolio, string $name, bool $isDefault, array $items): Strategy
	{
		if ($isDefault) {
			foreach ($this->getStrategies($user, $portfolio) as $strategy) {
				$strategy->isDefault = false;
				$this->strategyRepository->persist($strategy);
			}
		}

		$strategy = new Strategy(
			user: $user,
			portfolio: $portfolio,
			name: $name,
			isDefault: $isDefault,
			strategyItems: new ArrayIterator([]),
		);
		$this->strategyRepository->persist($strategy);

		$this->createStrategyItems($strategy, $items);

		return $strategy;
	}

	/** @param list<StrategyItemCreateDto> $items */
	public function updateStrategy(Strategy $strategy, string $name, bool $isDefault, array $items): Strategy
	{
		$user = $strategy->user;
		$portfolio = $strategy->portfolio;

		if ($isDefault && !$strategy->isDefault) {
			foreach ($this->getStrategies($user, $portfolio) as $otherStrategy) {
				if ($otherStrategy->id === $strategy->id) {
					continue;
				}

				$otherStrategy->isDefault = false;
				$this->strategyRepository->persist($otherStrategy);
			}
		}

		$strategy->name = $name;
		$strategy->isDefault = $isDefault;
		$this->strategyRepository->persist($strategy);

		$this->strategyItemRepository->deleteStrategyItems($strategy->id);
		$this->createStrategyItems($strategy, $items);

		return $strategy;
	}

	public function deleteStrategy(Strategy $strategy): void
	{
		$user = $strategy->user;
		$portfolio = $strategy->portfolio;
		$wasDefault = $strategy->isDefault;

		$this->strategyItemRepository->deleteStrategyItems($strategy->id);
		$this->strategyRepository->delete($strategy);

		if (!$wasDefault) {
			return;
		}

		$strategies = iterator_to_array($this->getStrategies($user, $portfolio), false);
		if (count($strategies) <= 0) {
			return;
		}

		$strategies[0]->isDefault = true;
		$this->strategyRepository->persist($strategies[0]);
	}

	/** @param list<StrategyItemCreateDto> $items */
	private function createStrategyItems(Strategy $strategy, array $items): void
	{
		foreach ($items as $itemDto) {
			if ($itemDto->isOthers) {
				continue;
			}

			$asset = $itemDto->assetId !== null
				? $this->assetRepository->findAsset($itemDto->assetId, $strategy->user->id)
				: null;

			$group = $itemDto->groupId !== null
				? $this->groupRepository->findGroup($strategy->user->id, $itemDto->groupId)
				: null;

			$strategyItem = new StrategyItem(
				strategy: $strategy,
				asset: $asset,
				group: $group,
				percentage: new Decimal((string) $itemDto->percentage),
			);
			$this->strategyItemRepository->persist($strategyItem);
		}
	}
}
