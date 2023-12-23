import { AEntity } from ".";

export class Dividend extends AEntity {
    assetId: number;
    brokerId: number;
    paidDate: Date;
    priceGross: number;
    priceNet: number;
    tax: number;
    currencyId: number;
    exchangeRate: number;
}
