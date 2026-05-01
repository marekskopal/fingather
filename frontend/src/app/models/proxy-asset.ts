import {TickerTypeEnum} from '@app/models/enums/ticker-type-enum';
import {Ticker} from '@app/models/ticker';

export interface ProxyAsset {
    id: number;
    tickerType: TickerTypeEnum;
    ticker: Ticker;
}
