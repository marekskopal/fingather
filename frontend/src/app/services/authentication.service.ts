import { HttpClient } from '@angular/common/http';
import {computed, effect, inject, Injectable, signal} from '@angular/core';
import { Router } from '@angular/router';
import { SignUp } from '@app/models';
import { Authentication } from '@app/models/authentication';
import { BoolResponse } from '@app/models/bool-response';
import {GoogleClientId} from "@app/models/google-client-id";
import { GoogleLoginResponse, isGoogleLoginRequiresCurrency } from '@app/models/google-login-response';
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

    public async googleClientId(): Promise<string> {
        const response = await firstValueFrom<GoogleClientId>(
            this.http.get<GoogleClientId>(`${environment.apiUrl}/authentication/google-client-id`),
        );

        return response.googleClientId;
    }

    public async googleLogin(idToken: string, defaultCurrencyId?: number): Promise<GoogleLoginResponse> {
        console.log("yop");
        const response = await firstValueFrom<GoogleLoginResponse>(
            this.http.post<GoogleLoginResponse>(`${environment.apiUrl}/authentication/google-login`, {
                idToken,
                defaultCurrencyId,
            }),
        );

        if (!isGoogleLoginRequiresCurrency(response)) {
            this.setAuthentication(response);
        }

        return response;
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
