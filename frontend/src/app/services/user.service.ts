import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { User } from '@app/models';
import {Observable} from "rxjs";
import {OkResponse} from "@app/models/ok-response";

@Injectable({ providedIn: 'root' })
export class UserService {

    public constructor(
        private http: HttpClient
    ) {
    }

    public create(user: User): Observable<User> {
        return this.http.post<User>(`${environment.apiUrl}/admin/user`, user);
    }

    public getAll(): Observable<User[]> {
        return this.http.get<User[]>(`${environment.apiUrl}/admin/user`);
    }

    public getById(id: number): Observable<User> {
        return this.http.get<User>(`${environment.apiUrl}/admin/user/${id}`);
    }

    public update(id: number, user: User): Observable<User> {
        return this.http.put<User>(`${environment.apiUrl}/admin/user/${id}`, user)
            .pipe(map(x => {
                return x;
            }));
    }

    public delete(id: number): Observable<OkResponse> {
        return this.http.delete<OkResponse>(`${environment.apiUrl}/admin/user/${id}`)
            .pipe(map(x => {
                // auto logout if the logged in user deleted their own record
                //if (id == this.userValue.id) {
                //    this.logout();
                //}
                return x;
            }));
    }
}
