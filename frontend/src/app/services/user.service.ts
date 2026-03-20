import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { User } from '@app/models';
import { OrderDirection } from '@app/models/enums/order-direction';
import { UserOrderBy } from '@app/models/enums/user-order-by';
import { OkResponse } from '@app/models/ok-response';
import { UserList } from '@app/models/user-list';
import { NotifyService } from '@app/services/notify-service';
import { buildHttpParams } from '@app/utils/http-params-builder';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class UserService extends NotifyService {
    private readonly http = inject(HttpClient);

    public createUser(user: User): Promise<User> {
        return firstValueFrom<User>(this.http.post<User>(`${environment.apiUrl}/admin/user`, user));
    }

    public getUsers(
        limit: number | null = null,
        offset: number | null = null,
        orderBy: UserOrderBy | null = null,
        orderDirection: OrderDirection | null = null,
    ): Promise<UserList> {
        return firstValueFrom<UserList>(
            this.http.get<UserList>(`${environment.apiUrl}/admin/user`, {
                params: buildHttpParams({ limit, offset, orderBy, orderDirection }),
            }),
        );
    }

    public getUser(id: number): Promise<User> {
        return firstValueFrom<User>(this.http.get<User>(`${environment.apiUrl}/admin/user/${id}`));
    }

    public updateUser(id: number, user: User): Promise<User> {
        return firstValueFrom<User>(
            this.http.put<User>(`${environment.apiUrl}/admin/user/${id}`, user),
        );
    }

    public deleteUser(id: number): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(this.http.delete<OkResponse>(`${environment.apiUrl}/admin/user/${id}`));
    }
}
