import { AbstractEntity } from '@app/models/abstract-entity';
import { GoalTypeEnum } from '@app/models/enums/goal-type-enum';

export interface Goal extends AbstractEntity {
    portfolioId: number;
    portfolioName: string;
    type: GoalTypeEnum;
    targetValue: string;
    deadline: string | null;
    isActive: boolean;
    achievedAt: string | null;
    currentValue: string;
    progressPercentage: number;
    createdAt: string;
}
