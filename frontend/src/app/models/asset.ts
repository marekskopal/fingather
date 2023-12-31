import {Ticker} from "@app/models/ticker";

export class Asset {
    public id: number;
    public tickerId: number;
    public groupId: number
    public price: number;
    public units: number;
    public value: number;
    public gain: number;
    public gainDefaultCurrency: number;
    public gainPercentage: number;
    public dividendGain: number;
    public dividendGainDefaultCurrency: number;
    public dividendGainPercentage: number;
    public fxImpact: number;
    public fxImpactPercentage: number;
    public return: number;
    public returnPercentage: number;

    public ticker: Ticker;
}
