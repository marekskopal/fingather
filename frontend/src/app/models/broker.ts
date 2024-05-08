import { AbstractEntity } from '@app/models/abstract-entity';
import { BrokerImportTypes } from '@app/models/enums/broker-import-type-enum';

export interface Broker extends AbstractEntity {
    userId: number;
    name: string;
    importType: BrokerImportTypes;
}
