import { AEntity } from ".";

export class Transaction extends AEntity {
    public assetId: string;
    public brokerId: string;
    public actionType: string
    public created: Date;
    public units: number;
    public priceUnit: number;
    public currency: string;
    public exchangeRate: number;
    public feeConversion: number;
    public notes: string;
}
