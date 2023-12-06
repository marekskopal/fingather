import {AEntity} from "./AEntity";

export class ADataEntity extends AEntity {
    date: Date;
    value: number;
    transactionValue: number;
    gain: number;
    gainPercentage: number;
    dividendGain: number;
    dividendGainPercentage: number;
    fxImpact: number;
    fxImpactPercentage: number;
    return: number;
    returnPercentage: number;
}
