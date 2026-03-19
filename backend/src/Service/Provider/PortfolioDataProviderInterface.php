<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;

interface PortfolioDataProviderInterface
{
	public function getPortfolioData(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): CalculatedDataDto;

	public function deletePortfolioData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void;
}
