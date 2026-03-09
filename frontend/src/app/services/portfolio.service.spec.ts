import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { Portfolio } from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { StorageService } from '@app/services/storage.service';
import { environment } from '@environments/environment';

import { PortfolioService } from './portfolio.service';

const mockPortfolio: Portfolio = { id: 1, name: 'Main', currencyId: 1, isDefault: true };
const mockPortfolio2: Portfolio = { id: 2, name: 'Secondary', currencyId: 2, isDefault: false };

describe('PortfolioService', () => {
    let service: PortfolioService;
    let httpMock: HttpTestingController;
    let storageSpy: { get: ReturnType<typeof vi.fn>; set: ReturnType<typeof vi.fn>; remove: ReturnType<typeof vi.fn> };

    beforeEach(() => {
        storageSpy = { get: vi.fn().mockReturnValue(null), set: vi.fn(), remove: vi.fn() };

        TestBed.configureTestingModule({
            providers: [
                PortfolioService,
                provideHttpClient(),
                provideHttpClientTesting(),
                { provide: StorageService, useValue: storageSpy },
            ],
        });
        service = TestBed.inject(PortfolioService);
        httpMock = TestBed.inject(HttpTestingController);
    });

    afterEach(() => {
        httpMock.verify();
    });

    describe('createPortfolio', () => {
        it('POSTs to /portfolios and returns the portfolio', async () => {
            const promise = service.createPortfolio(mockPortfolio);

            const req = httpMock.expectOne(`${environment.apiUrl}/portfolios`);
            expect(req.request.method).toBe('POST');
            req.flush(mockPortfolio);

            expect(await promise).toEqual(mockPortfolio);
        });
    });

    describe('getPortfolios', () => {
        it('GETs /portfolios and returns an array', async () => {
            const promise = service.getPortfolios();

            const req = httpMock.expectOne(`${environment.apiUrl}/portfolios`);
            expect(req.request.method).toBe('GET');
            req.flush([mockPortfolio, mockPortfolio2]);

            expect(await promise).toEqual([mockPortfolio, mockPortfolio2]);
        });
    });

    describe('getPortfolio', () => {
        it('GETs /portfolio/:id', async () => {
            const promise = service.getPortfolio(1);

            const req = httpMock.expectOne(`${environment.apiUrl}/portfolio/1`);
            expect(req.request.method).toBe('GET');
            req.flush(mockPortfolio);

            expect(await promise).toEqual(mockPortfolio);
        });
    });

    describe('getCurrentPortfolio', () => {
        it('returns portfolio from storage without HTTP when storage has one', async () => {
            storageSpy.get.mockReturnValue(mockPortfolio);

            const result = await service.getCurrentPortfolio();

            httpMock.expectNone(`${environment.apiUrl}/portfolio/default`);
            expect(result).toEqual(mockPortfolio);
        });

        it('fetches default portfolio via HTTP when storage is empty', async () => {
            storageSpy.get.mockReturnValue(null);

            const promise = service.getCurrentPortfolio();

            const req = httpMock.expectOne(`${environment.apiUrl}/portfolio/default`);
            req.flush(mockPortfolio);

            expect(await promise).toEqual(mockPortfolio);
            expect(storageSpy.set).toHaveBeenCalledWith('currentPortfolio', mockPortfolio);
        });

        it('returns in-memory cached portfolio on second call without storage or HTTP', async () => {
            storageSpy.get.mockReturnValue(null);

            const p1 = service.getCurrentPortfolio();
            httpMock.expectOne(`${environment.apiUrl}/portfolio/default`).flush(mockPortfolio);
            await p1;

            storageSpy.get.mockReturnValue(null); // storage ignored after in-memory cache set
            const result = await service.getCurrentPortfolio();
            httpMock.expectNone(`${environment.apiUrl}/portfolio/default`);
            expect(result).toEqual(mockPortfolio);
        });
    });

    describe('setCurrentPortfolio', () => {
        it('persists to storage and caches in memory', async () => {
            service.setCurrentPortfolio(mockPortfolio2);

            expect(storageSpy.set).toHaveBeenCalledWith('currentPortfolio', mockPortfolio2);

            // Subsequent getCurrentPortfolio should return from in-memory cache
            const result = await service.getCurrentPortfolio();
            httpMock.expectNone(`${environment.apiUrl}/portfolio/default`);
            expect(result).toEqual(mockPortfolio2);
        });
    });

    describe('cleanCurrentPortfolio', () => {
        it('removes from storage and clears in-memory cache', async () => {
            service.setCurrentPortfolio(mockPortfolio);
            service.cleanCurrentPortfolio();

            expect(storageSpy.remove).toHaveBeenCalledWith('currentPortfolio');

            // Next call must hit the network
            storageSpy.get.mockReturnValue(null);
            const p = service.getCurrentPortfolio();
            httpMock.expectOne(`${environment.apiUrl}/portfolio/default`).flush(mockPortfolio);
            await p;
        });
    });

    describe('getDefaultPortfolio', () => {
        it('GETs /portfolio/default', async () => {
            const promise = service.getDefaultPortfolio();

            const req = httpMock.expectOne(`${environment.apiUrl}/portfolio/default`);
            expect(req.request.method).toBe('GET');
            req.flush(mockPortfolio);

            expect(await promise).toEqual(mockPortfolio);
        });

        it('returns cached default portfolio on second call without HTTP', async () => {
            const p1 = service.getDefaultPortfolio();
            httpMock.expectOne(`${environment.apiUrl}/portfolio/default`).flush(mockPortfolio);
            await p1;

            const result = await service.getDefaultPortfolio();
            httpMock.expectNone(`${environment.apiUrl}/portfolio/default`);
            expect(result).toEqual(mockPortfolio);
        });
    });

    describe('updatePortfolio', () => {
        it('PUTs to /portfolio/:id', async () => {
            const promise = service.updatePortfolio(1, mockPortfolio);

            const req = httpMock.expectOne(`${environment.apiUrl}/portfolio/1`);
            expect(req.request.method).toBe('PUT');
            req.flush(mockPortfolio);

            expect(await promise).toEqual(mockPortfolio);
        });

        it('cleans current portfolio when updating the current one', async () => {
            service.setCurrentPortfolio(mockPortfolio);

            const promise = service.updatePortfolio(1, mockPortfolio);
            httpMock.expectOne(`${environment.apiUrl}/portfolio/1`).flush(mockPortfolio);
            await promise;

            expect(storageSpy.remove).toHaveBeenCalledWith('currentPortfolio');
        });

        it('does not clean current portfolio when updating a different one', async () => {
            service.setCurrentPortfolio(mockPortfolio);
            storageSpy.remove.mockClear();

            const promise = service.updatePortfolio(2, mockPortfolio2);
            httpMock.expectOne(`${environment.apiUrl}/portfolio/2`).flush(mockPortfolio2);
            await promise;

            expect(storageSpy.remove).not.toHaveBeenCalled();
        });
    });

    describe('deletePortfolio', () => {
        it('DELETEs /portfolio/:id', async () => {
            const ok: OkResponse = { code: 200, message: 'ok' };
            const promise = service.deletePortfolio(2);

            const req = httpMock.expectOne(`${environment.apiUrl}/portfolio/2`);
            expect(req.request.method).toBe('DELETE');
            req.flush(ok);

            expect(await promise).toEqual(ok);
        });

        it('cleans current portfolio when deleting the current one', async () => {
            service.setCurrentPortfolio(mockPortfolio);

            const promise = service.deletePortfolio(1);
            httpMock.expectOne(`${environment.apiUrl}/portfolio/1`).flush({ code: 200, message: 'ok' });
            await promise;

            expect(storageSpy.remove).toHaveBeenCalledWith('currentPortfolio');
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
