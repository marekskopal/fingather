<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\CostBasisMethodEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\CostBasisComparisonDto;
use FinGather\Service\DataCalculator\Dto\CostBasisComparisonRowDto;
use FinGather\Service\DataCalculator\Dto\TaxReportRealizedGainsDto;
use FinGather\Service\Tax\Jurisdiction\TaxJurisdictionRulesFactory;

final readonly class CostBasisComparisonCalculator
{
	public function __construct(
		private TaxReportRealizedGainsCalculatorInterface $realizedGainsCalculator,
		private TaxJurisdictionRulesFactory $jurisdictionFactory,
	) {
	}

	public function calculate(User $user, Portfolio $portfolio, int $year): CostBasisComparisonDto
	{
		$now = new DateTimeImmutable();
		$yearStart = new DateTimeImmutable($year . '-01-01');
		$yearEnd = $year === (int) $now->format('Y')
			? $now
			: new DateTimeImmutable($year . '-12-31 23:59:59');

		$rules = $this->jurisdictionFactory->forPortfolio($portfolio);
		$allowed = $rules->allowedCostBasisMethods();
		$rate = $portfolio->estimatedTaxRate ?? $rules->defaultEstimatedTaxRate();
		$grossExemption = $rules->annualGrossProceedsExemption();
		$gainExemption = $rules->annualGainExemption();

		/** @var array<string, TaxReportRealizedGainsDto> $resultByMethod */
		$resultByMethod = [];
		foreach (CostBasisMethodEnum::cases() as $method) {
			$resultByMethod[$method->value] = $this->realizedGainsCalculator->calculate($user, $portfolio, $yearStart, $yearEnd, $method);
		}

		$configuredResult = $resultByMethod[$portfolio->costBasisMethod->value];
		$configuredTaxableNet = $this->taxableNet($configuredResult, $grossExemption, $gainExemption);

		$rows = [];
		$optimalMethod = $portfolio->costBasisMethod;
		$optimalNet = $configuredResult->netRealizedGainLoss;

		foreach (CostBasisMethodEnum::cases() as $method) {
			$result = $resultByMethod[$method->value];
			$net = $result->netRealizedGainLoss;
			$taxableNet = $this->taxableNet($result, $grossExemption, $gainExemption);
			$estimatedTax = $rate !== null ? $taxableNet->mul($rate) : null;
			$deltaVsConfigured = $rate !== null
				? $configuredTaxableNet->mul($rate)->sub($taxableNet->mul($rate))
				: $configuredTaxableNet->sub($taxableNet);

			$rows[] = new CostBasisComparisonRowDto(
				method: $method,
				allowedInJurisdiction: in_array($method, $allowed, true),
				totalSalesProceeds: $result->totalSalesProceeds,
				totalCostBasis: $result->totalCostBasis,
				totalGains: $result->totalGains,
				totalLosses: $result->totalLosses,
				netRealizedGainLoss: $net,
				estimatedTax: $estimatedTax,
				deltaVsConfigured: $deltaVsConfigured,
			);

			if ($net >= $optimalNet) {
				continue;
			}

			$optimalMethod = $method;
			$optimalNet = $net;
		}

		return new CostBasisComparisonDto(
			year: $year,
			configuredMethod: $portfolio->costBasisMethod,
			optimalMethod: $optimalMethod,
			estimatedTaxRate: $rate,
			annualGainExemption: $gainExemption,
			annualGrossProceedsExemption: $grossExemption,
			rows: $rows,
		);
	}

	private function taxableNet(TaxReportRealizedGainsDto $result, ?Decimal $grossExemption, ?Decimal $gainExemption,): Decimal
	{
		if ($grossExemption !== null && $result->totalSalesProceeds <= $grossExemption) {
			return new Decimal(0);
		}

		$taxable = $this->maxZero($result->netRealizedGainLoss);
		if ($gainExemption !== null) {
			$taxable = $this->maxZero($taxable->sub($gainExemption));
		}

		return $taxable;
	}

	private function maxZero(Decimal $value): Decimal
	{
		return $value->isNegative() ? new Decimal(0) : $value;
	}
}
