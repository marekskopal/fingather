export interface PortfolioRiskData {
    volatility: number;
    maxDrawdown: number;
    sharpeRatio: number;
    beta: number;
    correlationLabels: string[];
    correlationMatrix: number[][];
}
