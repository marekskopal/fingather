import { HttpHandlerFn, HttpRequest, HttpResponse } from '@angular/common/http';
import { TestBed } from '@angular/core/testing';
import { AuthenticationService } from '@app/services/authentication.service';
import { environment } from '@environments/environment';
import { of } from 'rxjs';

import { jwtInterceptor } from './jwt.interceptor';

/**
 * Runs the request through jwtInterceptor with a stubbed AuthenticationService and
 * returns the Authorization header the interceptor put on the outgoing request.
 */
function authHeaderFor(url: string, loggedIn: boolean): string | null {
    TestBed.resetTestingModule();

    const authServiceSpy = {
        isLoggedIn: vi.fn().mockReturnValue(loggedIn),
        isImpersonating: vi.fn().mockReturnValue(false),
        authentication: vi.fn().mockReturnValue(loggedIn ? { accessToken: 'test-token' } : null),
    };

    TestBed.configureTestingModule({
        providers: [{ provide: AuthenticationService, useValue: authServiceSpy }],
    });

    let captured: HttpRequest<unknown> | undefined;
    const next: HttpHandlerFn = (req) => {
        captured = req;
        return of(new HttpResponse({ status: 200 }));
    };

    TestBed.runInInjectionContext(() => {
        jwtInterceptor(new HttpRequest('GET', url), next).subscribe();
    });

    return captured?.headers.get('Authorization') ?? null;
}

describe('jwtInterceptor', () => {
    it('attaches the bearer token to API requests when logged in', () => {
        expect(authHeaderFor(`${environment.apiUrl}/current-user`, true)).toBe('Bearer test-token');
    });

    // Regression: the MCP OAuth authorize endpoint is origin-relative (/mcp, not under
    // environment.apiUrl), but the backend identifies the consenting user from the bearer
    // token. Without the explicit allowlist the request goes out unauthenticated → 401 →
    // the app logs the user out and redirects to the login page instead of authorizing.
    it('attaches the bearer token to the MCP OAuth authorize endpoint', () => {
        expect(authHeaderFor('/mcp/oauth/authorize', true)).toBe('Bearer test-token');
    });

    it('does not attach the bearer token to other non-API endpoints', () => {
        expect(authHeaderFor('/mcp/oauth/client-info', true)).toBeNull();
        expect(authHeaderFor('/some/other/path', true)).toBeNull();
    });

    it('does not attach the bearer token when not logged in', () => {
        expect(authHeaderFor('/mcp/oauth/authorize', false)).toBeNull();
        expect(authHeaderFor(`${environment.apiUrl}/current-user`, false)).toBeNull();
    });
});
