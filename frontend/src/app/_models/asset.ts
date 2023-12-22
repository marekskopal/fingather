import {ticker} from "@app/_models/ticker";

export class Asset {
    id: number;
    tickerId: number;
    groupId: number
    price: number;
    units: number;
    value: number;
    gain: number;
    gainDefaultCurrency: number;
    gainPercentage: number;
    dividendGain: number;
    dividendGainDefaultCurrency: number;
    dividendGainPercentage: number;
    fxImpact: number;
    fxImpactPercentage: number;
    return: number;
    returnPercentage: number;

    ticker: ticker;
}
