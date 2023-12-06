import {Asset, AEntity} from "@app/_models";

export class Group extends AEntity {
    name: string;
    assets: Asset[];
}
