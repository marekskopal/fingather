import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { Group } from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { environment } from '@environments/environment';

import { GroupService } from './group.service';

const mockGroup: Group = { id: 1, name: 'Tech', color: 'blue', assets: [] };

describe('GroupService', () => {
    let service: GroupService;
    let httpMock: HttpTestingController;

    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [GroupService, provideHttpClient(), provideHttpClientTesting()],
        });
        service = TestBed.inject(GroupService);
        httpMock = TestBed.inject(HttpTestingController);
    });

    afterEach(() => {
        httpMock.verify();
    });

    describe('createGroup', () => {
        it('POSTs to /groups/:portfolioId and returns the group', async () => {
            const promise = service.createGroup(mockGroup, 1);

            const req = httpMock.expectOne(`${environment.apiUrl}/groups/1`);
            expect(req.request.method).toBe('POST');
            expect(req.request.body).toEqual(mockGroup);
            req.flush(mockGroup);

            expect(await promise).toEqual(mockGroup);
        });
    });

    describe('getGroups', () => {
        it('GETs /groups/:portfolioId and returns an array', async () => {
            const promise = service.getGroups(1);

            const req = httpMock.expectOne(`${environment.apiUrl}/groups/1`);
            expect(req.request.method).toBe('GET');
            req.flush([mockGroup]);

            expect(await promise).toEqual([mockGroup]);
        });
    });

    describe('getGroup', () => {
        it('GETs /group/:id', async () => {
            const promise = service.getGroup(1);

            const req = httpMock.expectOne(`${environment.apiUrl}/group/1`);
            expect(req.request.method).toBe('GET');
            req.flush(mockGroup);

            expect(await promise).toEqual(mockGroup);
        });
    });

    describe('getOthersGroup', () => {
        it('GETs /group/others/:portfolioId', async () => {
            const promise = service.getOthersGroup(1);

            const req = httpMock.expectOne(`${environment.apiUrl}/group/others/1`);
            expect(req.request.method).toBe('GET');
            req.flush(mockGroup);

            expect(await promise).toEqual(mockGroup);
        });
    });

    describe('updateGroup', () => {
        it('PUTs to /group/:id', async () => {
            const updated: Group = { ...mockGroup, name: 'Finance' };
            const promise = service.updateGroup(1, updated);

            const req = httpMock.expectOne(`${environment.apiUrl}/group/1`);
            expect(req.request.method).toBe('PUT');
            expect(req.request.body).toEqual(updated);
            req.flush(updated);

            expect(await promise).toEqual(updated);
        });
    });

    describe('deleteGroup', () => {
        it('DELETEs /group/:id', async () => {
            const ok: OkResponse = { code: 200, message: 'ok' };
            const promise = service.deleteGroup(1);

            const req = httpMock.expectOne(`${environment.apiUrl}/group/1`);
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
