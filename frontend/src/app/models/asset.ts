import { Ticker } from '@app/models/ticker';

export interface Asset {
    id: number;
    tickerId: number;
    groupId: number;
    price: number;

    ticker: Ticker;
}
