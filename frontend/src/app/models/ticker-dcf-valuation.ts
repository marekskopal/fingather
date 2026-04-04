export interface TickerDcfValuationHistoryPoint {
    fiscalDate: string;
    freeCashFlow: number | null;
    revenue: number | null;
}

export interface TickerDcfValuationInputs {
    sharesOutstanding: number;
    latestRevenue: number | null;
    latestFcfe: number | null;
    quarterlyRevenueGrowth: number | null;
    beta: number | null;
    history: TickerDcfValuationHistoryPoint[];
}

export interface TickerDcfValuationAssumptions {
    wacc: number;
    terminalGrowthRate: number;
    projectionYears: number;
    appliedGrowthRate: number;
    appliedFcfMargin: number;
}

export interface TickerDcfValuationProjection {
    projectedRevenues: number[];
    projectedFcfes: number[];
    terminalFcfe: number;
    terminalValue: string;
    discountedTerminalValue: string;
}

export type TickerDcfValuationStatus = 'overvalued' | 'undervalued' | 'fairlyValued';

export interface TickerDcfValuation {
    tickerId: number;
    intrinsicValue: string;
    equityValue: string;
    currentPrice: string | null;
    valuationDiffPercent: number | null;
    valuationStatus: TickerDcfValuationStatus | null;
    inputs: TickerDcfValuationInputs;
    assumptions: TickerDcfValuationAssumptions;
    projection: TickerDcfValuationProjection;
}
