import {AEntity} from '@app/models/AEntity';

export class Broker extends AEntity {
    public userId: number;
    public name: string;
    public importType: BrokerImportTypes;
}

export enum BrokerImportTypes {
    Trading212 = 'Trading212',
    Revolut = 'Revolut',
    Anycoin = 'Anycoin',
}
