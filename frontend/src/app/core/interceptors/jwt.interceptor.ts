import {
    HttpEvent, HttpHandler, HttpInterceptor, HttpRequest
} from '@angular/common/http';
import { Injectable } from '@angular/core';
import { AuthenticationService } from '@app/services/authentication.service';
import { environment } from '@environments/environment';
import { from, lastValueFrom, Observable } from 'rxjs';

@Injectable()
export class JwtInterceptor implements HttpInterceptor {
    private isRefreshing: boolean = false;

    public constructor(private authorizationService: AuthenticationService) { }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
        return from(this.handle(request, next));
    }

    private async handle(request: HttpRequest<any>, next: HttpHandler): Promise<HttpEvent<any>> {
        // wait for token refresh
        while (this.isRefreshing && request.url !== `${environment.apiUrl}/authentication/refresh-token`) {
            // eslint-disable-next-line no-await-in-loop, no-promise-executor-return
            await new Promise((r) => setTimeout(r, 10));
        }

        // add auth header with jwt if user is logged in and request is to the api url
        request = this.setAuthorizationHeader(request);

        try {
            return await lastValueFrom(next.handle(request));
        } catch (err: any) {
            if ([401, 403].includes(err.status) && this.authorizationService.$isLoggedIn()) {
                return this.handleRefreshToken(request, next);
            }

            const error = err.error?.message || err.statusText;
            // eslint-disable-next-line no-console
            console.error(error);
            throw error;
        }
    }

    private setAuthorizationHeader(request: HttpRequest<any>): HttpRequest<any> {
        if (!this.authorizationService.$isLoggedIn()) {
            return request;
        }

        const isApiUrl = request.url.startsWith(environment.apiUrl);
        if (!isApiUrl) {
            return request;
        }

        return request.clone({
            setHeaders: {
                Authorization: `Bearer ${this.authorizationService.$authentication()?.accessToken}`
            }
        });
    }

    private async handleRefreshToken(request: HttpRequest<any>, next: HttpHandler): Promise<HttpEvent<any>> {
        this.isRefreshing = true;

        try {
            await this.authorizationService.refreshToken();

            request = this.setAuthorizationHeader(request);

            this.isRefreshing = false;

            return await lastValueFrom(next.handle(request));
        } catch (error) {
            this.isRefreshing = false;

            this.authorizationService.logout();

            throw error;
        }
    }
}
