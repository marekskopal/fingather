import { AbstractEntity } from '@app/models/abstract-entity';
import { UserRoleEnum } from '@app/models/enums/user-role-enum';

export interface User extends AbstractEntity {
    email: string;
    password: string;
    name: string;
    defaultCurrencyId: number;
    role: UserRoleEnum;
    isEmailVerified: boolean;
    isOnboardingCompleted: boolean;
    lastLoggedIn: string | null;
    lastRefreshTokenGenerated: string | null;
    isEmailNotificationsEnabled: boolean;
}
