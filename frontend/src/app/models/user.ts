import {AEntity} from "@app/models/AEntity";

export class User extends AEntity {
    public email: string
    public password: string;
    public name: string;
    public defaultCurrencyId: number;
    public role: UserRoleEnum;
}

export enum UserRoleEnum {
    User = 'User',
    Admin = 'Admin',
}
