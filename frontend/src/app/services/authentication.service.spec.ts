import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { Router } from '@angular/router';
import { Authentication } from '@app/models/authentication';
import { CurrentUserService } from '@app/services/current-user.service';
import { PortfolioService } from '@app/services/portfolio.service';
import { StorageService } from '@app/services/storage.service';
import { environment } from '@environments/environment';

import { AuthenticationService } from './authentication.service';

const mockAuth: Authentication = {
    accessToken: 'access-token',
    refreshToken: 'refresh-token',
    userId: 1,
};

describe('AuthenticationService', () => {
    let service: AuthenticationService;
    let httpMock: HttpTestingController;
    let routerSpy: { navigate: ReturnType<typeof vi.fn> };
    let portfolioServiceSpy: { cleanCurrentPortfolio: ReturnType<typeof vi.fn> };
    let currentUserServiceSpy: { cleanCurrentUser: ReturnType<typeof vi.fn> };
    let storageServiceSpy: {
        get: ReturnType<typeof vi.fn>;
        set: ReturnType<typeof vi.fn>;
        remove: ReturnType<typeof vi.fn>;
    };

    beforeEach(() => {
        routerSpy = { navigate: vi.fn() };
        portfolioServiceSpy = { cleanCurrentPortfolio: vi.fn() };
        currentUserServiceSpy = { cleanCurrentUser: vi.fn() };
        storageServiceSpy = { get: vi.fn().mockReturnValue(null), set: vi.fn(), remove: vi.fn() };

        TestBed.configureTestingModule({
            providers: [
                AuthenticationService,
                provideHttpClient(),
                provideHttpClientTesting(),
                { provide: Router, useValue: routerSpy },
                { provide: PortfolioService, useValue: portfolioServiceSpy },
                { provide: CurrentUserService, useValue: currentUserServiceSpy },
                { provide: StorageService, useValue: storageServiceSpy },
            ],
        });

        service = TestBed.inject(AuthenticationService);
        httpMock = TestBed.inject(HttpTestingController);
    });

    afterEach(() => {
        httpMock.verify();
    });

    describe('isLoggedIn signal', () => {
        it('is false when authentication is null', () => {
            service.authentication.set(null);
            expect(service.isLoggedIn()).toBe(false);
        });

        it('is true when authentication is set', () => {
            service.authentication.set(mockAuth);
            expect(service.isLoggedIn()).toBe(true);
        });
    });

    describe('login', () => {
        it('POSTs credentials, stores via StorageService, and sets signal', async () => {
            const promise = service.login('user@example.com', 'secret');

            const req = httpMock.expectOne(`${environment.apiUrl}/authentication/login`);
            expect(req.request.method).toBe('POST');
            expect(req.request.body).toEqual({ email: 'user@example.com', password: 'secret' });
            req.flush(mockAuth);

            const result = await promise;
            expect(result).toEqual(mockAuth);
            expect(service.authentication()).toEqual(mockAuth);
            expect(storageServiceSpy.set).toHaveBeenCalledWith('authentication', mockAuth);
        });
    });

    describe('logout', () => {
        it('removes from StorageService, nulls signal, and navigates to login', () => {
            service.authentication.set(mockAuth);

            service.logout();

            expect(storageServiceSpy.remove).toHaveBeenCalledWith('authentication');
            expect(service.authentication()).toBeNull();
            expect(portfolioServiceSpy.cleanCurrentPortfolio).toHaveBeenCalled();
            expect(currentUserServiceSpy.cleanCurrentUser).toHaveBeenCalled();
            expect(routerSpy.navigate).toHaveBeenCalledWith(['/authentication/login']);
        });
    });

    describe('refreshToken', () => {
        it('POSTs the refresh token, updates StorageService and signal', async () => {
            const newAuth: Authentication = { accessToken: 'new-access', refreshToken: 'new-refresh', userId: 1 };
            service.authentication.set(mockAuth);

            const promise = service.refreshToken();

            const req = httpMock.expectOne(`${environment.apiUrl}/authentication/refresh-token`);
            expect(req.request.method).toBe('POST');
            expect(req.request.body).toEqual({ refreshToken: mockAuth.refreshToken });
            req.flush(newAuth);

            const result = await promise;
            expect(result).toEqual(newAuth);
            expect(service.authentication()).toEqual(newAuth);
            expect(storageServiceSpy.set).toHaveBeenCalledWith('authentication', newAuth);
        });
    });
});
