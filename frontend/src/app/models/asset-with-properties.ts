import { Ticker } from '@app/models/ticker';

export interface AssetWithProperties {
    id: number;
    tickerId: number;
    groupId: number;
    price: number;
    units: number;
    value: number;
    transactionValue: number;
    transactionValueDefaultCurrency: number;
    averagePrice: number;
    averagePriceDefaultCurrency: number;
    gain: number;
    gainDefaultCurrency: number;
    realizedGain: number;
    realizedGainDefaultCurrency: number;
    gainPercentage: number;
    gainPercentagePerAnnum: number;
    dividendYield: number;
    dividendYieldDefaultCurrency: number;
    dividendYieldPercentage: number;
    dividendYieldPercentagePerAnnum: number;
    fxImpact: number;
    fxImpactPercentage: number;
    fxImpactPercentagePerAnnum: number;
    return: number;
    returnPercentage: number;
    returnPercentagePerAnnum: number;
    tax: number;
    taxDefaultCurrency: number;
    fee: number;
    feeDefaultCurrency: number;
    percentage: number;

    ticker: Ticker;
}
