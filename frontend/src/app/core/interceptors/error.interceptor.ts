import { HttpErrorResponse, HttpEvent, HttpHandlerFn, HttpRequest } from '@angular/common/http';
import { inject } from '@angular/core';
import { AlertService } from '@app/services/alert.service';
import { environment } from '@environments/environment';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';

export function errorInterceptor(req: HttpRequest<unknown>, next: HttpHandlerFn): Observable<HttpEvent<unknown>> {
    const alertService = inject(AlertService);

    if (!req.url.startsWith(environment.apiUrl)) {
        return next(req);
    }

    return next(req).pipe(
        catchError((err: HttpErrorResponse) => {
            // 401 is handled by jwtInterceptor (token refresh / logout)
            if (err.status === 401) {
                return throwError(() => err);
            }

            const message: string = err.error?.message ?? 'An unexpected error occurred.';
            alertService.error(message);

            return throwError(() => err);
        }),
    );
}
