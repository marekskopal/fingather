export class Broker {
    id: string;
    userId: number;
    name: string;
    importType: BrokerImportTypes;
}

export enum BrokerImportTypes {
    Trading212,
    Revolut,
}
