export interface StrategyComparisonItem {
    name: string;
    color: string | null;
    assetId: number | null;
    groupId: number | null;
    isOthers: boolean;
    targetPercentage: number;
    actualPercentage: number;
    differencePercentage: number;
}
