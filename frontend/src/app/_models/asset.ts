import {ticker} from "@app/_models/ticker";

export class Asset {
    id: string;
    tickerId: string;
    groupId: string|null
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
