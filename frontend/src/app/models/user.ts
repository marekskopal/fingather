import {AEntity} from '@app/models/AEntity';
import {UserRoleEnum} from '@app/models/enums/user-role-enum';

export class User extends AEntity {
    public email: string
    public password: string;
    public name: string;
    public defaultCurrencyId: number;
    public role: UserRoleEnum;
}
