import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { User, UserWithStatistic } from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class UserService extends NotifyService {
    private readonly http = inject(HttpClient);

    public createUser(user: User): Promise<User> {
        return firstValueFrom<User>(this.http.post<User>(`${environment.apiUrl}/admin/user`, user));
    }

    public getUsers(): Promise<UserWithStatistic[]> {
        return firstValueFrom<UserWithStatistic[]>(
            this.http.get<UserWithStatistic[]>(`${environment.apiUrl}/admin/user`),
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
