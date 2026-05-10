export type TaxJurisdiction = 'CzechRepublic' | 'Slovakia' | 'Germany' | 'Generic';

export type CostBasisMethod = 'Fifo' | 'Lifo' | 'AverageCost';

export interface PortfolioTaxSettings {
    portfolioId: number;
    taxJurisdiction: TaxJurisdiction;
    costBasisMethod: CostBasisMethod;
    estimatedTaxRate: string | null;
    longTermHoldingDays: number | null;
    defaultEstimatedTaxRate: string | null;
    annualGainExemption: string | null;
    annualGrossProceedsExemption: string | null;
    allowedCostBasisMethods: CostBasisMethod[];
}

export interface PortfolioTaxSettingsUpdate {
    taxJurisdiction: TaxJurisdiction;
    costBasisMethod: CostBasisMethod;
    estimatedTaxRate: string | null;
}
