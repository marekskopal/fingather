import { AbstractEntity } from '@app/models/abstract-entity';

export interface Currency extends AbstractEntity {
    code: string;
    name: string;
    symbol: string;
}
