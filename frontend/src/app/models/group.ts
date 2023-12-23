import {Asset, AEntity} from "@app/models";

export class Group extends AEntity {
    name: string;
    assets: Asset[];
}
