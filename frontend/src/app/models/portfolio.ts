import { AbstractEntity } from '@app/models/abstract-entity';

export interface Portfolio extends AbstractEntity {
    currencyId: number;
    name: string;
    isDefault: boolean;
}
