import { AbstractEntity } from '@app/models/abstract-entity';
import {AbstractGroupDataEntity} from "@app/models/abstract-group-data-entity";

export interface AbstractGroupWithGroupDataEntity extends AbstractEntity {
    userId: number;
    name: string;
    percentage: number;
    color?: string;
    groupData: AbstractGroupDataEntity;
}
