import {AEntity} from "@app/models/AEntity";

export class User extends AEntity {
    email: string
    password: string;
    name: string;
    role: UserRoleEnum;
}

export enum UserRoleEnum {
    User = 'User',
    Admin = 'Admin',
}
