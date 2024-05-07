import { AssetWithProperties } from '@app/models';
import { AbstractEntity } from '@app/models/abstract-entity';

export interface Group extends AbstractEntity {
    name: string;
    color: string;
    assets: AssetWithProperties[];
}
