import { AbstractEntity } from '@app/models/abstract-entity';

export interface AbstractDataEntity extends AbstractEntity {
    date: string;
    value: number;
    transactionValue: number;
    gain: number;
    gainPercentage: number;
    gainPercentagePerAnnum: number;
    realizedGain: number;
    dividendGain: number;
    dividendGainPercentage: number;
    dividendGainPercentagePerAnnum: number;
    fxImpact: number;
    fxImpactPercentage: number;
    fxImpactPercentagePerAnnum: number;
    return: number;
    returnPercentage: number;
    returnPercentagePerAnnum: number;
    tax: number;
    fee: number;
}
