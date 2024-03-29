import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { User, UserWithStatistic } from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';

@Injectable({ providedIn: 'root' })
export class UserService extends NotifyService {
    public constructor(
        private http: HttpClient
    ) {
        super();
    }

    public createUser(user: User): Observable<User> {
        return this.http.post<User>(`${environment.apiUrl}/admin/user`, user);
    }

    public getUsers(): Observable<UserWithStatistic[]> {
        return this.http.get<UserWithStatistic[]>(`${environment.apiUrl}/admin/user`);
    }

    public getUser(id: number): Observable<User> {
        return this.http.get<User>(`${environment.apiUrl}/admin/user/${id}`);
    }

    public updateUser(id: number, user: User): Observable<User> {
        return this.http.put<User>(`${environment.apiUrl}/admin/user/${id}`, user)
            .pipe(map((x) => x));
    }

    public deleteUser(id: number): Observable<OkResponse> {
        return this.http.delete<OkResponse>(`${environment.apiUrl}/admin/user/${id}`);
    }
}
