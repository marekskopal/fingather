﻿import {AEntity,AssetWithProperties} from '@app/models';

export class Group extends AEntity {
    public name: string;
    public assets: AssetWithProperties[];
}
