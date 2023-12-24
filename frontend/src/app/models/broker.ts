export class Broker {
    id: number;
    userId: number;
    name: string;
    importType: BrokerImportTypes;
}

export enum BrokerImportTypes {
    Trading212 = 'Trading212',
    Revolut = 'Revolut',
    Anycoin = 'Anycoin',
}
