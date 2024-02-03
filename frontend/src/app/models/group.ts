import { AssetWithProperties } from '@app/models';
import { AEntity } from '@app/models/AEntity';

export class Group extends AEntity {
    public name: string;
    public color: string;
    public assets: AssetWithProperties[];
}
