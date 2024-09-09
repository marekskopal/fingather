import { AbstractEntity } from '@app/models/abstract-entity';

export interface AbstractGroupDataEntity extends AbstractEntity {
    value: string;
    transactionValue: string;
    gain: number;
    gainPercentage: number;
    gainPercentagePerAnnum: number;
    dividendYield: number;
    dividendYieldPercentage: number;
    dividendYieldPercentagePerAnnum: number;
    fxImpact: number;
    fxImpactPercentage: number;
    fxImpactPercentagePerAnnum: number;
    return: number;
    returnPercentage: number;
    returnPercentagePerAnnum: number;
}
