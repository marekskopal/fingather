import { AbstractEntity } from '@app/models/abstract-entity';
import { Ticker } from '@app/models/ticker';

export interface Transaction extends AbstractEntity {
    assetId: string;
    brokerId: string | null;
    actionType: TransactionActionType;
    actionCreated: string;
    createType: TransactionCreateType;
    created: Date;
    modified: Date;
    units: string;
    price: string;
    currencyId: number;
    tax: string;
    taxCurrencyId: number;
    fee: string;
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
