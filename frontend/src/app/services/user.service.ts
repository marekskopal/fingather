﻿import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { User } from '@app/models';

@Injectable({ providedIn: 'root' })
export class UserService {

    constructor(
        private http: HttpClient
    ) {
    }

    create(user: User) {
        return this.http.post(`${environment.apiUrl}/admin/user`, user);
    }

    getAll() {
        return this.http.get<User[]>(`${environment.apiUrl}/admin/user`);
    }

    getById(id: number) {
        return this.http.get<User>(`${environment.apiUrl}/admin/user/${id}`);
    }

    update(id: number, user: User) {
        return this.http.put(`${environment.apiUrl}/admin/user/${id}`, user)
            .pipe(map(x => {
                return x;
            }));
    }

    delete(id: number) {
        return this.http.delete(`${environment.apiUrl}/admin/user/${id}`)
            .pipe(map(x => {
                // auto logout if the logged in user deleted their own record
                //if (id == this.userValue.id) {
                //    this.logout();
                //}
                return x;
            }));
    }
}
