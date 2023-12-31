import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { User } from '@app/models';

@Injectable({ providedIn: 'root' })
export class UserService {

    public constructor(
        private http: HttpClient
    ) {
    }

    public create(user: User) {
        return this.http.post(`${environment.apiUrl}/admin/user`, user);
    }

    public getAll() {
        return this.http.get<User[]>(`${environment.apiUrl}/admin/user`);
    }

    public getById(id: number) {
        return this.http.get<User>(`${environment.apiUrl}/admin/user/${id}`);
    }

    public update(id: number, user: User) {
        return this.http.put(`${environment.apiUrl}/admin/user/${id}`, user)
            .pipe(map(x => {
                return x;
            }));
    }

    public delete(id: number) {
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
