export interface StrategyRebalancingRequest {
    cashToInvest: string;
    cashCurrencyId: number | null;
    allowSelling: boolean;
}
