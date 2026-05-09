export type TaxJurisdiction = 'CzechRepublic' | 'Generic';

export type CostBasisMethod = 'Fifo' | 'Lifo' | 'AverageCost';

export interface PortfolioTaxSettings {
    portfolioId: number;
    taxJurisdiction: TaxJurisdiction;
    costBasisMethod: CostBasisMethod;
    estimatedTaxRate: string | null;
    longTermHoldingDays: number | null;
    defaultEstimatedTaxRate: string | null;
    allowedCostBasisMethods: CostBasisMethod[];
}

export interface PortfolioTaxSettingsUpdate {
    taxJurisdiction: TaxJurisdiction;
    costBasisMethod: CostBasisMethod;
    estimatedTaxRate: string | null;
}
