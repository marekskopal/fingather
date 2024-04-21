import { Ticker } from '@app/models/ticker';

export class AssetWithProperties {
    public id: number;
    public tickerId: number;
    public groupId: number;
    public price: number;
    public units: number;
    public value: number;
    public transactionValue: number;
    public transactionValueDefaultCurrency: number;
    public averagePrice: number;
    public averagePriceDefaultCurrency: number;
    public gain: number;
    public gainDefaultCurrency: number;
    public realizedGain: number;
    public realizedGainDefaultCurrency: number;
    public gainPercentage: number;
    public gainPercentagePerAnnum: number;
    public dividendGain: number;
    public dividendGainDefaultCurrency: number;
    public dividendGainPercentage: number;
    public dividendGainPercentagePerAnnum: number;
    public fxImpact: number;
    public fxImpactPercentage: number;
    public fxImpactPercentagePerAnnum: number;
    public return: number;
    public returnPercentage: number;
    public returnPercentagePerAnnum: number;
    public tax: number;
    public taxDefaultCurrency: number;
    public fee: number;
    public feeDefaultCurrency: number;

    public ticker: Ticker;
}
