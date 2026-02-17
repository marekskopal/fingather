<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\PortfolioRepository;
use Iterator;

final readonly class PortfolioProvider implements PortfolioProviderInterface
{
	public function __construct(
		private PortfolioRepository $portfolioRepository,
		private GroupProvider $groupProvider,
		private DataProvider $dataProvider,
	) {
	}

	/** @return Iterator<Portfolio> */
	public function getPortfolios(User $user): Iterator
	{
		return $this->portfolioRepository->findPortfolios($user->id);
	}

	/** @return list<Portfolio> */
	public function getOtherPortfolios(User $user, Portfolio $portfolio): array
	{
		return array_values(array_filter(
			iterator_to_array($this->getPortfolios($user), false),
			fn (Portfolio $otherPortfolio) => $otherPortfolio->id !== $portfolio->id,
		));
	}

	public function getPortfolio(User $user, int $portfolioId): ?Portfolio
	{
		return $this->portfolioRepository->findPortfolio($user->id, $portfolioId);
	}

	public function getDefaultPortfolio(User $user): Portfolio
	{
		return $this->portfolioRepository->findDefaultPortfolio($user->id);
	}

	public function createPortfolio(User $user, Currency $currency, string $name, bool $isDefault): Portfolio
	{
		if ($isDefault) {
			foreach ($this->getPortfolios($user) as $portfolio) {
				$portfolio->isDefault = false;
				$this->portfolioRepository->persist($portfolio);
			}
		}

		$portfolio = new Portfolio(user: $user, currency: $currency, name: $name, isDefault: $isDefault);
		$this->portfolioRepository->persist($portfolio);

		$this->groupProvider->createOthersGroup(user: $user, portfolio: $portfolio);

		return $portfolio;
	}

	public function createDefaultPortfolio(User $user, Currency $currency): Portfolio
	{
		return $this->createPortfolio(user: $user, currency: $currency, name: 'My Portfolio', isDefault: true);
	}

	public function updatePortfolio(Portfolio $portfolio, Currency $currency, string $name, bool $isDefault): Portfolio
	{
		$otherPortfolios = $this->getOtherPortfolios(user: $portfolio->user, portfolio: $portfolio);
		if (!$isDefault && count($otherPortfolios) === 0) {
			$isDefault = true;
		}

		if ($isDefault && $portfolio->isDefault !== $isDefault) {
			foreach ($otherPortfolios as $otherPortfolio) {
				$otherPortfolio->isDefault = false;
				$this->portfolioRepository->persist($otherPortfolio);
			}
		}

		$recalculateTransactions = false;
		if ($currency->id !== $portfolio->currency->id) {
			$recalculateTransactions = true;
		}

		$portfolio->currency = $currency;
		$portfolio->name = $name;
		$portfolio->isDefault = $isDefault;
		$this->portfolioRepository->persist($portfolio);

		if ($recalculateTransactions) {
			$this->dataProvider->deleteUserData(user: $portfolio->user, portfolio: $portfolio, recalculateTransactions: true);
		}

		return $portfolio;
	}

	public function deletePortfolio(Portfolio $portfolio): void
	{
		$otherPortfolios = $this->getOtherPortfolios(user: $portfolio->user, portfolio: $portfolio);
		if (count($otherPortfolios) === 0) {
			return;
		}

		if ($portfolio->isDefault) {
			$otherPortfolio = array_first($otherPortfolios);
			$otherPortfolio->isDefault = true;
			$this->portfolioRepository->persist($otherPortfolio);
		}

		$this->portfolioRepository->delete($portfolio);
	}
}
