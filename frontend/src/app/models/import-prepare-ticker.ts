import { Ticker } from '@app/models/ticker';

export class ImportPrepareTicker {
    public brokerId: number;
    public ticker: string;
    public tickers: Ticker[];
}
