import { AEntity } from ".";

export class Transaction extends AEntity {
    public assetId: string;
    public brokerId: string;
    public actionType: TransactionActionType;
    public actionCreated: Date;
    public createType: TransactionCreateType
    public created: Date;
    public modified: Date;
    public units: number;
    public price: number;
    public currencyId: number;
    public exchangeRate: number;
    public tax: number;
    public notes: string;
}

export enum TransactionActionType {
    Undefined = 'Undefined',
    Buy = 'Buy',
    Sell = 'Sell',
    Dividend = 'Dividend',
}

export enum TransactionCreateType {
    Manual = 'Manual',
    Import = 'Import',
}
