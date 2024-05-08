import { AbstractEntity } from '@app/models/abstract-entity';
import { Ticker } from '@app/models/ticker';

export interface Transaction extends AbstractEntity {
    assetId: string;
    brokerId: string;
    actionType: TransactionActionType;
    actionCreated: string;
    createType: TransactionCreateType;
    created: Date;
    modified: Date;
    units: number;
    price: number;
    currencyId: number;
    tax: number;
    taxCurrencyId: number;
    fee: number;
    feeCurrencyId: number;
    notes: string;
    importIdentifier: string;
    ticker: Ticker;
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
