import {
    HttpErrorResponse, HttpEvent, HttpHandlerFn, HttpRequest,
} from '@angular/common/http';
import { inject } from '@angular/core';
import { Authentication } from '@app/models/authentication';
import { AuthenticationService } from '@app/services/authentication.service';
import { environment } from '@environments/environment';
import { BehaviorSubject, catchError, filter, from, Observable, switchMap, take, throwError } from 'rxjs';

const refreshTokenUrl = `${environment.apiUrl}/authentication/refresh-token` as const;

let isRefreshing = false;
const refreshTokenSubject = new BehaviorSubject<string | null>(null);

export function jwtInterceptor(req: HttpRequest<unknown>, next: HttpHandlerFn): Observable<HttpEvent<unknown>> {
    const authService = inject(AuthenticationService);

    req = addAuthHeader(req, authService);

    return next(req).pipe(
        catchError((err: HttpErrorResponse) => {
            if (
                [401, 403].includes(err.status)
                && authService.isLoggedIn()
                && req.url !== refreshTokenUrl
            ) {
                return handleTokenRefresh(req, next, authService);
            }

            return throwError(() => err);
        }),
    );
}

function addAuthHeader(req: HttpRequest<unknown>, authService: AuthenticationService): HttpRequest<unknown> {
    if (!authService.isLoggedIn()) {
        return req;
    }

    if (!req.url.startsWith(environment.apiUrl)) {
        return req;
    }

    return req.clone({
        setHeaders: {
            Authorization: `Bearer ${authService.authentication()?.accessToken}`,
        },
    });
}

function handleTokenRefresh(
    req: HttpRequest<unknown>,
    next: HttpHandlerFn,
    authService: AuthenticationService,
): Observable<HttpEvent<unknown>> {
    if (!isRefreshing) {
        isRefreshing = true;
        refreshTokenSubject.next(null);

        return from(authService.refreshToken()).pipe(
            switchMap((auth: Authentication) => {
                isRefreshing = false;
                refreshTokenSubject.next(auth.accessToken);
                return next(addAuthHeader(req, authService));
            }),
            catchError((err) => {
                isRefreshing = false;
                authService.logout();
                return throwError(() => err);
            }),
        );
    }

    return refreshTokenSubject.pipe(
        filter((token): token is string => token !== null),
        take(1),
        switchMap(() => next(addAuthHeader(req, authService))),
    );
}
