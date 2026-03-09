import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { User } from '@app/models';
import { UserRoleEnum } from '@app/models/enums/user-role-enum';
import { environment } from '@environments/environment';

import { CurrentUserService } from './current-user.service';

const mockUser: User = {
    id: 1,
    email: 'john@example.com',
    password: '',
    name: 'John Doe',
    defaultCurrencyId: 1,
    role: UserRoleEnum.User,
    isEmailVerified: true,
    isOnboardingCompleted: true,
    lastLoggedIn: null,
    lastRefreshTokenGenerated: null,
    isEmailNotificationsEnabled: false,
};

describe('CurrentUserService', () => {
    let service: CurrentUserService;
    let httpMock: HttpTestingController;

    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [CurrentUserService, provideHttpClient(), provideHttpClientTesting()],
        });
        service = TestBed.inject(CurrentUserService);
        httpMock = TestBed.inject(HttpTestingController);
    });

    afterEach(() => {
        httpMock.verify();
    });

    describe('getCurrentUser', () => {
        it('GETs /current-user and returns the user', async () => {
            const promise = service.getCurrentUser();

            const req = httpMock.expectOne(`${environment.apiUrl}/current-user`);
            expect(req.request.method).toBe('GET');
            req.flush(mockUser);

            expect(await promise).toEqual(mockUser);
        });

        it('returns cached user on second call without a second HTTP request', async () => {
            const p1 = service.getCurrentUser();
            httpMock.expectOne(`${environment.apiUrl}/current-user`).flush(mockUser);
            await p1;

            const result = await service.getCurrentUser();
            httpMock.expectNone(`${environment.apiUrl}/current-user`);
            expect(result).toEqual(mockUser);
        });
    });

    describe('updateCurrentUser', () => {
        it('PUTs to /current-user and returns updated user', async () => {
            const update = {
                name: 'Jane',
                email: 'jane@example.com',
                password: 'secret',
                isEmailNotificationsEnabled: true,
            };
            const updated: User = { ...mockUser, name: 'Jane', email: 'jane@example.com' };

            const promise = service.updateCurrentUser(update);

            const req = httpMock.expectOne(`${environment.apiUrl}/current-user`);
            expect(req.request.method).toBe('PUT');
            expect(req.request.body).toEqual(update);
            req.flush(updated);

            expect(await promise).toEqual(updated);
        });

        it('refreshes cache: subsequent getCurrentUser returns updated user without HTTP', async () => {
            const update = { name: 'Jane', email: 'jane@example.com', password: '', isEmailNotificationsEnabled: false };
            const updated: User = { ...mockUser, name: 'Jane' };

            const p = service.updateCurrentUser(update);
            httpMock.expectOne(`${environment.apiUrl}/current-user`).flush(updated);
            await p;

            const result = await service.getCurrentUser();
            httpMock.expectNone(`${environment.apiUrl}/current-user`);
            expect(result.name).toBe('Jane');
        });
    });

    describe('deleteCurrentUser', () => {
        it('DELETEs /current-user', async () => {
            const promise = service.deleteCurrentUser();

            const req = httpMock.expectOne(`${environment.apiUrl}/current-user`);
            expect(req.request.method).toBe('DELETE');
            req.flush(null);

            await promise;
        });

        it('clears cache: subsequent getCurrentUser makes a new HTTP request', async () => {
            // Pre-warm cache
            const p1 = service.getCurrentUser();
            httpMock.expectOne(`${environment.apiUrl}/current-user`).flush(mockUser);
            await p1;

            // Delete clears cache
            const del = service.deleteCurrentUser();
            httpMock.expectOne(`${environment.apiUrl}/current-user`).flush(null);
            await del;

            // Next call must hit the network again
            const p2 = service.getCurrentUser();
            httpMock.expectOne(`${environment.apiUrl}/current-user`).flush(mockUser);
            await p2;
        });
    });

    describe('cleanCurrentUser', () => {
        it('clears cache so next getCurrentUser makes a new HTTP request', async () => {
            const p1 = service.getCurrentUser();
            httpMock.expectOne(`${environment.apiUrl}/current-user`).flush(mockUser);
            await p1;

            service.cleanCurrentUser();

            const p2 = service.getCurrentUser();
            httpMock.expectOne(`${environment.apiUrl}/current-user`).flush(mockUser);
            await p2;
        });
    });
});
