<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

enum TaxOptimizationRationaleEnum: string
{
	case HarvestBeforeLongTerm = 'harvest_before_long_term';
	case HarvestGenericLoss = 'harvest_generic_loss';
	case HoldForTaxFreeGain = 'hold_for_tax_free_gain';
	case LossNoLongerDeductible = 'loss_no_longer_deductible';
	case AlreadyTaxFree = 'already_tax_free';
	case WinningShortTerm = 'winning_short_term';
}
