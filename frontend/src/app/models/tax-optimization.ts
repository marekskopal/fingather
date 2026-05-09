import { TaxJurisdiction } from '@app/models/portfolio-tax-settings';

export type TaxOptimizationRationale =
    | 'harvest_before_long_term'
    | 'harvest_generic_loss'
    | 'hold_for_tax_free_gain'
    | 'loss_no_longer_deductible'
    | 'already_tax_free'
    | 'winning_short_term';

export interface TaxOptimizationSuggestion {
    assetId: number;
    tickerTicker: string;
    tickerName: string;
    tickerLogo: string | null;
    firstBuyDate: string;
    holdingPeriodDays: number;
    daysUntilLongTerm: number | null;
    units: number;
    marketValue: number;
    costBasis: number;
    unrealizedGainLoss: number;
    estimatedTaxImpact: number | null;
    rationale: TaxOptimizationRationale;
    holdingVariesByBroker: boolean;
}

export interface TaxOptimization {
    asOfDate: string;
    jurisdiction: TaxJurisdiction;
    longTermHoldingDays: number | null;
    estimatedTaxRate: number | null;
    harvestNow: TaxOptimizationSuggestion[];
    holdForTaxFreeGain: TaxOptimizationSuggestion[];
    lossNoLongerDeductible: TaxOptimizationSuggestion[];
    alreadyTaxFree: TaxOptimizationSuggestion[];
    winningShortTerm: TaxOptimizationSuggestion[];
    estimatedTaxSavedByHarvestingNow: number;
    estimatedTaxSavedByWaiting: number;
}
