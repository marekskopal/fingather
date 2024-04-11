import { AEntity } from '@app/models/AEntity';

export class ADataEntity extends AEntity {
    public date: string;
    public value: number;
    public transactionValue: number;
    public gain: number;
    public gainPercentage: number;
    public gainPercentagePerAnnum: number;
    public realizedGain: number;
    public dividendGain: number;
    public dividendGainPercentage: number;
    public dividendGainPercentagePerAnnum: number;
    public fxImpact: number;
    public fxImpactPercentage: number;
    public fxImpactPercentagePerAnnum: number;
    public return: number;
    public returnPercentage: number;
    public returnPercentagePerAnnum: number;
    public tax: number;
    public fee: number;
}
