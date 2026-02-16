import {Ticker} from "@app/models/ticker";

export interface DividendCalendarItem {
    assetId: number;
    ticker: Ticker;
    exDate: string;
    amountPerShare: string;
    units: string;
    totalAmount: string;
    totalAmountDefaultCurrency: string;
}
