<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use Iterator;

interface PortfolioProviderInterface
{
	/** @return Iterator<Portfolio> */
	public function getPortfolios(User $user): Iterator;

	/** @return list<Portfolio> */
	public function getOtherPortfolios(User $user, Portfolio $portfolio): array;

	public function getPortfolio(User $user, int $portfolioId): ?Portfolio;

	public function getDefaultPortfolio(User $user): Portfolio;

	public function createPortfolio(User $user, Currency $currency, string $name, bool $isDefault): Portfolio;

	public function createDefaultPortfolio(User $user, Currency $currency): Portfolio;

	public function updatePortfolio(Portfolio $portfolio, Currency $currency, string $name, bool $isDefault): Portfolio;

	public function deletePortfolio(Portfolio $portfolio): void;
}
