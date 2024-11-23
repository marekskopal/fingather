import { HttpClient } from '@angular/common/http';
import {computed, effect, inject, Injectable, signal} from '@angular/core';
import { Router } from '@angular/router';
import { SignUp } from '@app/models';
import { Authentication } from '@app/models/authentication';
import { BoolResponse } from '@app/models/bool-response';
import { CurrentUserService } from '@app/services/current-user.service';
import { PortfolioService } from '@app/services/portfolio.service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class AuthenticationService {
    private readonly router = inject(Router);
    private readonly http = inject(HttpClient);
    private readonly portfolioService = inject(PortfolioService);
    private readonly currentUserService = inject(CurrentUserService);

    public authentication = signal<Authentication | null>(null);
    public isLoggedIn = computed<boolean>(
        () => this.authentication() !== null,
    );

    public constructor() {
        effect(() => {
            const localStorageAuthentication = localStorage.getItem('authentication');
            const authentication = localStorageAuthentication !== null ? JSON.parse(localStorageAuthentication) : null;

            this.authentication.set(authentication);
        }, {
            allowSignalWrites: true,
        });
    }

    public async login(email: string, password: string): Promise<Authentication> {
        const authentication = await firstValueFrom<Authentication>(
            this.http.post<Authentication>(`${environment.apiUrl}/authentication/login`, {
                email,
                password,
            }),
        );

        return this.setAuthentication(authentication);
    }

    public logout(): void {
        // remove authentication from local storage and set current authentication to null
        localStorage.removeItem('authentication');
        this.authentication.set(null);
        this.portfolioService.cleanCurrentPortfolio();
        this.currentUserService.cleanCurrentUser();
        this.router.navigate(['/authentication/login']);
    }

    public async signUp(signUp: SignUp): Promise<Authentication> {
        const authentication = await firstValueFrom<Authentication>(
            this.http.post<Authentication>(`${environment.apiUrl}/authentication/sign-up`, signUp),
        );

        return this.setAuthentication(authentication);
    }

    public async isEmailExists(email: string): Promise<boolean> {
        const response = await firstValueFrom<BoolResponse>(
            this.http.post<BoolResponse>(`${environment.apiUrl}/authentication/email-exists`, {
                email,
            }),
        );

        return response.value;
    }

    public async refreshToken(): Promise<Authentication> {
        const authentication = await firstValueFrom<Authentication>(
            this.http.post<Authentication>(
                `${environment.apiUrl}/authentication/refresh-token`,
                {
                    refreshToken: this.authentication()?.refreshToken,
                },
                { withCredentials: true },
            ),
        );

        localStorage.setItem('authentication', JSON.stringify(authentication));
        this.authentication.set(authentication);

        return authentication;
    }

    private setAuthentication(authentication: Authentication): Authentication {
        localStorage.setItem('authentication', JSON.stringify(authentication));
        this.authentication.set(authentication);
        this.portfolioService.cleanCurrentPortfolio();
        this.currentUserService.cleanCurrentUser();

        return authentication;
    }
}
