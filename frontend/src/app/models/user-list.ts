import { UserWithStatistic } from '@app/models/user-with-statistic';

export interface UserList {
    users: UserWithStatistic[];
    count: number;
}
