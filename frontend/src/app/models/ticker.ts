import {Market} from "@app/models/market";

export class Ticker {
    id: number;
    ticker: string;
    name: string;
    marketId: number;

    market: Market
}
