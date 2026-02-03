import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { User } from '@app/models';
import { environment } from '@environments/environment';
import { lastValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class CurrentUserService {
    private readonly http = inject(HttpClient);

    private currentUser: User | null = null;

    public async getCurrentUser(): Promise<User> {
        if (this.currentUser !== null) {
            return this.currentUser;
        }

        this.currentUser = await lastValueFrom<User>(
            this.http.get<User>(`${environment.apiUrl}/current-user`),
        );

        return this.currentUser;
    }

    public async updateCurrentUser(data: { isEmailNotificationsEnabled: boolean }): Promise<User> {
        this.currentUser = await lastValueFrom<User>(
            this.http.put<User>(`${environment.apiUrl}/current-user`, data),
        );

        return this.currentUser;
    }

    public cleanCurrentUser(): void {
        this.currentUser = null;
    }
}
