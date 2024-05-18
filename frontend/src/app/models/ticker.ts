import { TickerTypeEnum } from '@app/models/enums/ticker-type-enum';
import { Market } from '@app/models/market';

export interface Ticker {
    id: number;
    ticker: string;
    name: string;
    marketId: number;
    currencyId: number;
    type: TickerTypeEnum;
    isin: string | null;
    logo: string | null;
    sector: string | null;
    industry: string | null;
    website: string | null;
    description: string | null;
    country: string | null;
    market: Market;
}
