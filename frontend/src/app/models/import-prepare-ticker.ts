import { Ticker } from '@app/models/ticker';

export interface ImportPrepareTicker {
    brokerId: number;
    ticker: string;
    tickers: Ticker[];
}
