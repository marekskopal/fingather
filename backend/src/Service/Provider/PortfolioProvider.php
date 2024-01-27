<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\PortfolioRepository;

class PortfolioProvider
{
	public function __construct(private readonly PortfolioRepository $portfolioRepository)
	{
	}

	/** @return iterable<Portfolio> */
	public function getPortfolios(User $user): iterable
	{
		return $this->portfolioRepository->findPortfolios($user->getId());
	}

	public function getGroup(User $user, int $portfolioId): ?Portfolio
	{
		return $this->portfolioRepository->findPortfolio($user->getId(), $portfolioId);
	}

	public function getDefaultPortfolio(User $user): Portfolio
	{
		return $this->portfolioRepository->findDefaultPortfolio($user->getId());
	}

	public function createPortfolio(User $user, string $name): Portfolio
	{
		$portfolio = new Portfolio(user: $user, name: $name, isDefault: false);
		$this->portfolioRepository->persist($portfolio);

		return $portfolio;
	}

	public function createDefaultPortfolio(User $user): Portfolio
	{
		$portfolio = new Portfolio(user: $user, name: 'My Portfolio', isDefault: true);
		$this->portfolioRepository->persist($portfolio);

		return $portfolio;
	}

	public function updatePortfolio(Portfolio $portfolio, string $name): Portfolio
	{
		$portfolio->setName($name);
		$this->portfolioRepository->persist($portfolio);

		return $portfolio;
	}

	public function deletePortfolio(Portfolio $portfolio): void
	{
		$this->portfolioRepository->delete($portfolio);
	}
}
