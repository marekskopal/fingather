<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\TaxReportRealizedGainsDto;

interface TaxReportRealizedGainsCalculatorInterface
{
	public function calculate(
		User $user,
		Portfolio $portfolio,
		DateTimeImmutable $yearStart,
		DateTimeImmutable $yearEnd,
	): TaxReportRealizedGainsDto;
}
