import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { User } from '@app/models';

@Injectable({ providedIn: 'root' })
export class CurrentUserService {
    private currentUser: User|null = null;

    public constructor(
        private http: HttpClient
    ) {
    }

    public async getCurrentUser(): Promise<User> {
        if (this.currentUser !== null) {
            return this.currentUser;
        }

        this.currentUser = await this.http.get<User>(`${environment.apiUrl}/current-user`).toPromise();

        return this.currentUser;
    }
}
