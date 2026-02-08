import { AbstractEntity } from '@app/models/abstract-entity';

export interface StrategyItem extends AbstractEntity {
    strategyId: number;
    assetId: number | null;
    groupId: number | null;
    isOthers: boolean;
    percentage: number;
    name: string;
}
