import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { User } from '@app/models';
import { environment } from '@environments/environment';
import { lastValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class CurrentUserService {
    private currentUser: User | null = null;

    public constructor(
        private http: HttpClient
    ) {
    }

    public async getCurrentUser(): Promise<User> {
        if (this.currentUser !== null) {
            return this.currentUser;
        }

        this.currentUser = await lastValueFrom<User>(
            this.http.get<User>(`${environment.apiUrl}/current-user`)
        );

        return this.currentUser;
    }
}
