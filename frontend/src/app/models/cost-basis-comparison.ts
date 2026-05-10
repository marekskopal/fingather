import { CostBasisMethod } from '@app/models/portfolio-tax-settings';

export interface CostBasisComparisonRow {
    method: CostBasisMethod;
    allowedInJurisdiction: boolean;
    totalSalesProceeds: number;
    totalCostBasis: number;
    totalGains: number;
    totalLosses: number;
    netRealizedGainLoss: number;
    estimatedTax: number | null;
    deltaVsConfigured: number | null;
}

export interface CostBasisComparison {
    year: number;
    configuredMethod: CostBasisMethod;
    optimalMethod: CostBasisMethod;
    estimatedTaxRate: number | null;
    annualGainExemption: string | null;
    annualGrossProceedsExemption: string | null;
    rows: CostBasisComparisonRow[];
}
