import { StrategyComparisonItem } from '@app/models/strategy-comparison-item';

export interface StrategyWithComparison {
    id: number;
    name: string;
    isDefault: boolean;
    comparisonItems: StrategyComparisonItem[];
}
