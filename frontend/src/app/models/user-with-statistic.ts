import { AbstractEntity } from '@app/models/abstract-entity';
import { UserRoleEnum } from '@app/models/enums/user-role-enum';

export interface UserWithStatistic extends AbstractEntity {
    email: string;
    password: string;
    name: string;
    defaultCurrencyId: number;
    role: UserRoleEnum;
    assetCount: number;
    transactionCount: number;
}
