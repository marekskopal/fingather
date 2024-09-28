<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\PortfolioRepository;

class PortfolioProvider
{
	public function __construct(
		private readonly PortfolioRepository $portfolioRepository,
		private readonly GroupProvider $groupProvider,
		private readonly DataProvider $dataProvider,
	)
	{
	}

	/** @return list<Portfolio> */
	public function getPortfolios(User $user): array
	{
		return $this->portfolioRepository->findPortfolios($user->getId());
	}

	/** @return list<Portfolio> */
	public function getOtherPortfolios(User $user, Portfolio $portfolio): array
	{
		return array_values(array_filter(
			$this->getPortfolios($user),
			fn (Portfolio $otherPortfolio) => $otherPortfolio->getId() !== $portfolio->getId(),
		));
	}

	public function getPortfolio(User $user, int $portfolioId): ?Portfolio
	{
		return $this->portfolioRepository->findPortfolio($user->getId(), $portfolioId);
	}

	public function getDefaultPortfolio(User $user): Portfolio
	{
		return $this->portfolioRepository->findDefaultPortfolio($user->getId());
	}

	public function createPortfolio(User $user, Currency $currency, string $name, bool $isDefault): Portfolio
	{
		if ($isDefault) {
			foreach ($this->getPortfolios($user) as $portfolio) {
				$portfolio->setIsDefault(false);
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
		$otherPortfolios = $this->getOtherPortfolios(user: $portfolio->getUser(), portfolio: $portfolio);
		if (!$isDefault && count($otherPortfolios) === 0) {
			$isDefault = true;
		}

		if ($isDefault && $portfolio->getIsDefault() !== $isDefault) {
			foreach ($otherPortfolios as $otherPortfolio) {
				$otherPortfolio->setIsDefault(false);
				$this->portfolioRepository->persist($otherPortfolio);
			}
		}

		$recalculateTransactions = false;
		if ($currency->getId() !== $portfolio->getCurrency()->getId()) {
			$recalculateTransactions = true;
		}

		$portfolio->setCurrency($currency);
		$portfolio->setName($name);
		$portfolio->setIsDefault($isDefault);
		$this->portfolioRepository->persist($portfolio);

		if ($recalculateTransactions) {
			$this->dataProvider->deleteUserData(user: $portfolio->getUser(), portfolio: $portfolio, recalculateTransactions: true);
		}

		return $portfolio;
	}

	public function deletePortfolio(Portfolio $portfolio): void
	{
		$this->portfolioRepository->delete($portfolio);
	}
}
