import {AssetTicker} from "@app/_models/assetTicker";

export class Asset {
    id: string;
    assetTickerId: string;
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

    assetTicker: AssetTicker;
}
