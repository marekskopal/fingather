import { Ticker } from '@app/models/ticker';

export class Asset {
    public id: number;
    public tickerId: number;
    public groupId: number;
    public price: number;

    public ticker: Ticker;
}
