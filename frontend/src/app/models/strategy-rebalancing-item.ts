export interface StrategyRebalancingItem {
    name: string;
    color: string | null;
    assetId: number | null;
    groupId: number | null;
    isOthers: boolean;
    targetPercentage: number;
    actualPercentage: number;
    differencePercentage: number;
    currentValue: string;
    targetValue: string;
    suggestedTradeValue: string;
    suggestedTradeUnits: string | null;
    currentPrice: string | null;
}
