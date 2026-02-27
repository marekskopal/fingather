import { HttpClient, HttpParams } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { User } from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { UserList } from '@app/models/user-list';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class UserService extends NotifyService {
    private readonly http = inject(HttpClient);

    public createUser(user: User): Promise<User> {
        return firstValueFrom<User>(this.http.post<User>(`${environment.apiUrl}/admin/user`, user));
    }

    public getUsers(limit: number | null = null, offset: number | null = null): Promise<UserList> {
        let params = new HttpParams();
        if (limit !== null) {
            params = params.set('limit', limit);
        }
        if (offset !== null) {
            params = params.set('offset', offset);
        }
        return firstValueFrom<UserList>(
            this.http.get<UserList>(`${environment.apiUrl}/admin/user`, { params }),
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
