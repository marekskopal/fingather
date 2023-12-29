import {AEntity} from "@app/models/AEntity";

export class Broker extends AEntity {
    userId: number;
    name: string;
    importType: BrokerImportTypes;
}

export enum BrokerImportTypes {
    Trading212 = 'Trading212',
    Revolut = 'Revolut',
    Anycoin = 'Anycoin',
}
