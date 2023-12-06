import { AEntity } from ".";

export class Dividend extends AEntity {
    assetId: string;
    brokerId: string;
    paidDate: Date;
    priceGross: number;
    priceNet: number;
    tax: number;
    currencyId: number;
    exchangeRate: number;
}
