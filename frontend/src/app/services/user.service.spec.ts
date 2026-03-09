import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { User } from '@app/models';
import { OrderDirection } from '@app/models/enums/order-direction';
import { UserOrderBy } from '@app/models/enums/user-order-by';
import { UserRoleEnum } from '@app/models/enums/user-role-enum';
import { OkResponse } from '@app/models/ok-response';
import { UserList } from '@app/models/user-list';
import { UserWithStatistic } from '@app/models/user-with-statistic';
import { environment } from '@environments/environment';

import { UserService } from './user.service';

const mockUser: User = {
    id: 1,
    email: 'alice@example.com',
    password: '',
    name: 'Alice',
    defaultCurrencyId: 1,
    role: UserRoleEnum.User,
    isEmailVerified: true,
    isOnboardingCompleted: true,
    lastLoggedIn: null,
    lastRefreshTokenGenerated: null,
    isEmailNotificationsEnabled: false,
};

const mockUserWithStatistic: UserWithStatistic = {
    id: 1,
    email: 'alice@example.com',
    password: '',
    name: 'Alice',
    defaultCurrencyId: 1,
    role: UserRoleEnum.User,
    assetCount: 3,
    transactionCount: 10,
    lastLoggedIn: null,
    lastRefreshTokenGenerated: null,
};

const mockUserList: UserList = { users: [mockUserWithStatistic], count: 1 };

describe('UserService', () => {
    let service: UserService;
    let httpMock: HttpTestingController;

    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [UserService, provideHttpClient(), provideHttpClientTesting()],
        });
        service = TestBed.inject(UserService);
        httpMock = TestBed.inject(HttpTestingController);
    });

    afterEach(() => {
        httpMock.verify();
    });

    describe('createUser', () => {
        it('POSTs to /admin/user and returns the user', async () => {
            const promise = service.createUser(mockUser);

            const req = httpMock.expectOne(`${environment.apiUrl}/admin/user`);
            expect(req.request.method).toBe('POST');
            expect(req.request.body).toEqual(mockUser);
            req.flush(mockUser);

            expect(await promise).toEqual(mockUser);
        });
    });

    describe('getUsers', () => {
        it('GETs /admin/user with no params when all are null', async () => {
            const promise = service.getUsers();

            const req = httpMock.expectOne(`${environment.apiUrl}/admin/user`);
            expect(req.request.method).toBe('GET');
            expect(req.request.params.keys()).toHaveLength(0);
            req.flush(mockUserList);

            expect(await promise).toEqual(mockUserList);
        });

        it('sends limit and offset params when provided', async () => {
            const promise = service.getUsers(10, 20);

            const req = httpMock.expectOne((r) => r.url === `${environment.apiUrl}/admin/user`);
            expect(req.request.params.get('limit')).toBe('10');
            expect(req.request.params.get('offset')).toBe('20');
            req.flush(mockUserList);

            await promise;
        });

        it('sends orderBy and orderDirection params when provided', async () => {
            const promise = service.getUsers(null, null, UserOrderBy.Email, OrderDirection.Asc);

            const req = httpMock.expectOne((r) => r.url === `${environment.apiUrl}/admin/user`);
            expect(req.request.params.get('orderBy')).toBe(UserOrderBy.Email);
            expect(req.request.params.get('orderDirection')).toBe(OrderDirection.Asc);
            req.flush(mockUserList);

            await promise;
        });
    });

    describe('getUser', () => {
        it('GETs /admin/user/:id', async () => {
            const promise = service.getUser(1);

            const req = httpMock.expectOne(`${environment.apiUrl}/admin/user/1`);
            expect(req.request.method).toBe('GET');
            req.flush(mockUser);

            expect(await promise).toEqual(mockUser);
        });
    });

    describe('updateUser', () => {
        it('PUTs to /admin/user/:id', async () => {
            const promise = service.updateUser(1, mockUser);

            const req = httpMock.expectOne(`${environment.apiUrl}/admin/user/1`);
            expect(req.request.method).toBe('PUT');
            expect(req.request.body).toEqual(mockUser);
            req.flush(mockUser);

            expect(await promise).toEqual(mockUser);
        });
    });

    describe('deleteUser', () => {
        it('DELETEs /admin/user/:id', async () => {
            const ok: OkResponse = { code: 200, message: 'ok' };
            const promise = service.deleteUser(1);

            const req = httpMock.expectOne(`${environment.apiUrl}/admin/user/1`);
            expect(req.request.method).toBe('DELETE');
            req.flush(ok);

            expect(await promise).toEqual(ok);
        });
    });

    describe('NotifyService integration', () => {
        it('calls subscribers when notify() is invoked', () => {
            const cb = vi.fn();
            service.subscribe(cb);
            service.notify();
            expect(cb).toHaveBeenCalledTimes(1);
        });
    });
});
