import { DividendDataAsset } from '@app/models/dividend-data-asset';

export interface DividendDataDateInterval {
    interval: string;
    dividendDataAssets: DividendDataAsset[];
}
