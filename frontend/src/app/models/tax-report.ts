export interface TaxReport {
    year: number;
    realizedGains: TaxReportRealizedGains;
    unrealizedPositions: TaxReportUnrealized;
    dividends: TaxReportDividends;
    totalFees: number;
    totalTaxes: number;
}

export interface TaxReportRealizedGains {
    totalSalesProceeds: number;
    totalCostBasis: number;
    totalGains: number;
    totalLosses: number;
    totalFees: number;
    netRealizedGainLoss: number;
    transactions: TaxReportRealizedGainTransaction[];
}

export interface TaxReportRealizedGainTransaction {
    tickerTicker: string;
    tickerName: string;
    buyDate: string;
    sellDate: string;
    holdingPeriodDays: number;
    units: number;
    buyPrice: number;
    sellPrice: number;
    costBasis: number;
    salesProceeds: number;
    fee: number;
    gainLoss: number;
}

export interface TaxReportUnrealized {
    totalMarketValue: number;
    totalCostBasis: number;
    totalGainLoss: number;
    positions: TaxReportUnrealizedPosition[];
}

export interface TaxReportUnrealizedPosition {
    tickerTicker: string;
    tickerName: string;
    firstBuyDate: string;
    holdingPeriodDays: number;
    units: number;
    buyPrice: number;
    costBasis: number;
    marketValue: number;
    gainLoss: number;
}

export interface TaxReportDividends {
    totalGross: number;
    totalTax: number;
    totalNet: number;
    dividendsByCountry: TaxReportDividendsByCountry[];
    transactions: TaxReportDividendTransaction[];
}

export interface TaxReportDividendsByCountry {
    countryName: string;
    countryIsoCode: string;
    totalGross: number;
    totalTax: number;
    totalNet: number;
}

export interface TaxReportDividendTransaction {
    tickerTicker: string;
    tickerName: string;
    countryName: string;
    countryIsoCode: string;
    date: string;
    grossAmount: number;
    tax: number;
    netAmount: number;
}
