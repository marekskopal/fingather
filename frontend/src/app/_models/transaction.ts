import { AEntity } from ".";

export class Transaction extends AEntity {
    id: string;
    assetId: string;
    brokerId: string;
    actionType: string
    created: Date;
    units: number;
    priceUnit: number;
    currency: string;
    exchangeRate: number;
    feeConversion: number;
    notes: string;
}
