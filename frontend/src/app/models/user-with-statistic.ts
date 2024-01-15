import {AEntity} from "@app/models/AEntity";
import {UserRoleEnum} from "@app/models/user";

export class UserWithStatistic extends AEntity {
    public email: string
    public password: string;
    public name: string;
    public defaultCurrencyId: number;
    public role: UserRoleEnum;
    public assetCount: number;
    public transactionCount: number;
}
