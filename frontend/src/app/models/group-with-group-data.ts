import { AssetWithProperties} from '@app/models';
import {AbstractGroupWithGroupDataEntity} from "@app/models/abstract-group-with-group-data-entity";

export interface GroupWithGroupData extends AbstractGroupWithGroupDataEntity {
    color: string;
    assetIds: number[];
    assets: AssetWithProperties[];
}
