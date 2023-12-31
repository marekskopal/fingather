import { AEntity } from ".";

export class Dividend extends AEntity {
    public assetId: number;
    public brokerId: number;
    public paidDate: Date;
    public priceGross: number;
    public priceNet: number;
    public tax: number;
    public currencyId: number;
    public exchangeRate: number;
}
