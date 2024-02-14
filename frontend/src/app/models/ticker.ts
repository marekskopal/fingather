import { Market } from '@app/models/market';

export class Ticker {
    public id: number;
    public ticker: string;
    public name: string;
    public marketId: number;
    public currencyId: number;
    public logo: string | null;
    public market: Market;
}
