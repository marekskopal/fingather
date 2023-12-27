export class User {
    id: number;
    email: string
    password: string;
    name: string;
    role: UserRoleEnum;
}

export enum UserRoleEnum {
    User = 'User',
    Admin = 'Admin',
}
