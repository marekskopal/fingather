<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

enum TaxOptimizationBucketEnum: string
{
	case HarvestNow = 'HarvestNow';
	case HoldForTaxFreeGain = 'HoldForTaxFreeGain';
	case LossNoLongerDeductible = 'LossNoLongerDeductible';
	case AlreadyTaxFree = 'AlreadyTaxFree';
	case WinningShortTerm = 'WinningShortTerm';
}
