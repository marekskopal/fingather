import { AbstractEntity } from '@app/models/abstract-entity';
import { StrategyItem } from '@app/models/strategy-item';

export interface Strategy extends AbstractEntity {
    userId: number;
    portfolioId: number;
    name: string;
    isDefault: boolean;
    items: StrategyItem[];
}
