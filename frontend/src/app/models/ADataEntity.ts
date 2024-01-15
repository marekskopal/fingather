import {AEntity} from "./AEntity";

export class ADataEntity extends AEntity {
    public date: string;
    public value: number;
    public transactionValue: number;
    public gain: number;
    public gainPercentage: number;
    public dividendGain: number;
    public dividendGainPercentage: number;
    public fxImpact: number;
    public fxImpactPercentage: number;
    public return: number;
    public returnPercentage: number;
}
