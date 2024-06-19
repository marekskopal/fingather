import { HttpClient } from '@angular/common/http';
import { computed, Injectable, signal } from '@angular/core';
import { Router } from '@angular/router';
import { SignUp } from '@app/models';
import { Authentication } from '@app/models/authentication';
import { BoolResponse } from '@app/models/bool-response';
import { OkResponse } from '@app/models/ok-response';
import { CurrentUserService } from '@app/services/current-user.service';
import { PortfolioService } from '@app/services/portfolio.service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class AuthenticationService {
    public $authentication = signal<Authentication | null>(null);
    public $isLoggedIn = computed<boolean>(
        () => this.$authentication() !== null
    );

    public constructor(
        private readonly router: Router,
        private readonly http: HttpClient,
        private readonly portfolioService: PortfolioService,
        private readonly currentUserService: CurrentUserService,
    ) {
        const localStorageAuthentication = localStorage.getItem('authentication');
        const authentication = localStorageAuthentication !== null ? JSON.parse(localStorageAuthentication) : null;

        this.$authentication.set(authentication);
    }

    public async login(email: string, password: string): Promise<Authentication> {
        const authentication = await firstValueFrom<Authentication>(
            this.http.post<Authentication>(`${environment.apiUrl}/authentication/login`, {
                email,
                password,
            })
        );

        // store user details and jwt token in local storage to keep user logged in between page refreshes
        localStorage.setItem('authentication', JSON.stringify(authentication));
        this.$authentication.set(authentication);
        this.portfolioService.cleanCurrentPortfolio();
        this.currentUserService.cleanCurrentUser();

        return authentication;
    }

    public logout(): void {
        // remove authentication from local storage and set current authentication to null
        localStorage.removeItem('authentication');
        this.$authentication.set(null);
        this.portfolioService.cleanCurrentPortfolio();
        this.currentUserService.cleanCurrentUser();
        this.router.navigate(['/authentication/login']);
    }

    public signUp(signUp: SignUp): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(
            this.http.post<OkResponse>(`${environment.apiUrl}/authentication/sign-up`, signUp)
        );
    }

    public async isEmailExists(email: string): Promise<boolean> {
        const response = await firstValueFrom<BoolResponse>(
            this.http.post<BoolResponse>(`${environment.apiUrl}/authentication/email-exists`, {
                email,
            })
        );

        return response.value;
    }

    public async refreshToken(): Promise<Authentication> {
        const authentication = await firstValueFrom<Authentication>(
            this.http.post<Authentication>(
                `${environment.apiUrl}/authentication/refresh-token`,
                {
                    refreshToken: this.$authentication()?.refreshToken,
                },
                { withCredentials: true }
            )
        );

        localStorage.setItem('authentication', JSON.stringify(authentication));
        this.$authentication.set(authentication);

        return authentication;
    }
}
