import { StrategyRebalancingItem } from '@app/models/strategy-rebalancing-item';

export interface StrategyRebalancing {
    id: number;
    name: string;
    portfolioValue: string;
    cashToInvest: string;
    items: StrategyRebalancingItem[];
}
