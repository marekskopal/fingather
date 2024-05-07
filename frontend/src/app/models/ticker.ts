import { Market } from '@app/models/market';

export interface Ticker {
    id: number;
    ticker: string;
    name: string;
    marketId: number;
    currencyId: number;
    logo: string | null;
    market: Market;
}
