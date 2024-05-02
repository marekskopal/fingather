import { AEntity } from '@app/models/AEntity';
import {Ticker} from "@app/models/ticker";

export class Transaction extends AEntity {
    public assetId: string;
    public brokerId: string;
    public actionType: TransactionActionType;
    public actionCreated: string;
    public createType: TransactionCreateType;
    public created: Date;
    public modified: Date;
    public units: number;
    public price: number;
    public currencyId: number;
    public tax: number;
    public taxCurrencyId: number;
    public fee: number;
    public feeCurrencyId: number;
    public notes: string;
    public importIdentifier: string;
    public ticker: Ticker;
}

export enum TransactionActionType {
    Undefined = 'Undefined',
    Buy = 'Buy',
    Sell = 'Sell',
    Dividend = 'Dividend',
    Tax = 'Tax',
    Fee = 'Fee',
    DividendTax = 'DividendTax',
}

export enum TransactionCreateType {
    Manual = 'Manual',
    Import = 'Import',
}
