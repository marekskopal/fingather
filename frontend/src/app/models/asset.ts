import { Ticker } from '@app/models/ticker';
import { TickerDcfValuationStatus } from '@app/models/ticker-dcf-valuation';

export interface Asset {
    id: number;
    tickerId: number;
    groupId: number;
    price: number;
    dcfValuationDiffPercent: number | null;
    dcfValuationStatus: TickerDcfValuationStatus | null;

    ticker: Ticker;
}
