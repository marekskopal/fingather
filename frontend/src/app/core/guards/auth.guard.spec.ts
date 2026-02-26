import { TestBed } from '@angular/core/testing';
import { ActivatedRouteSnapshot, Router, RouterStateSnapshot } from '@angular/router';
import { AuthenticationService } from '@app/services/authentication.service';

import { AuthGuard } from './auth.guard';

describe('AuthGuard', () => {
    let guard: AuthGuard;
    let routerSpy: { navigate: ReturnType<typeof vi.fn> };
    let isLoggedInFn: ReturnType<typeof vi.fn>;

    beforeEach(() => {
        isLoggedInFn = vi.fn();
        routerSpy = { navigate: vi.fn() };

        TestBed.configureTestingModule({
            providers: [
                AuthGuard,
                { provide: Router, useValue: routerSpy },
                { provide: AuthenticationService, useValue: { isLoggedIn: isLoggedInFn } },
            ],
        });

        guard = TestBed.inject(AuthGuard);
    });

    it('returns true when the user is logged in', () => {
        isLoggedInFn.mockReturnValue(true);

        const route = {} as ActivatedRouteSnapshot;
        const state = { url: '/dashboard' } as RouterStateSnapshot;

        expect(guard.canActivate(route, state)).toBe(true);
        expect(routerSpy.navigate).not.toHaveBeenCalled();
    });

    it('returns false and navigates to login when the user is not logged in', () => {
        isLoggedInFn.mockReturnValue(false);

        const route = {} as ActivatedRouteSnapshot;
        const state = { url: '/protected' } as RouterStateSnapshot;

        expect(guard.canActivate(route, state)).toBe(false);
        expect(routerSpy.navigate).toHaveBeenCalledWith(
            ['/authentication/login'],
            { queryParams: { returnUrl: '/protected' } },
        );
    });
});
