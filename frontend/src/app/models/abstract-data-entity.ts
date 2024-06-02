import { AbstractEntity } from '@app/models/abstract-entity';

export interface AbstractDataEntity extends AbstractEntity {
    date: string;
    value: string;
    transactionValue: string;
    gain: number;
    gainPercentage: number;
    gainPercentagePerAnnum: number;
    realizedGain: number;
    dividendYield: number;
    dividendYieldPercentage: number;
    dividendYieldPercentagePerAnnum: number;
    fxImpact: number;
    fxImpactPercentage: number;
    fxImpactPercentagePerAnnum: number;
    return: number;
    returnPercentage: number;
    returnPercentagePerAnnum: number;
    tax: number;
    fee: number;
}
