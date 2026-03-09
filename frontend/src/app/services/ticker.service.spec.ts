import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { TickerTypeEnum } from '@app/models/enums/ticker-type-enum';
import { Market } from '@app/models/market';
import { Ticker } from '@app/models/ticker';
import { environment } from '@environments/environment';

import { TickerService } from './ticker.service';

const mockMarket: Market = {
    id: 1, name: 'NYSE', acronym: 'NYSE', mic: 'XNYS',
    country: 'US', city: 'New York', timezone: 'America/New_York', currencyId: 1,
};

const mockTicker: Ticker = {
    id: 1, ticker: 'AAPL', name: 'Apple Inc.', marketId: 1, currencyId: 1,
    type: TickerTypeEnum.Stock, isin: null, logo: null, sector: null, industry: null,
    website: null, description: null, country: null, market: mockMarket,
};

const mockTickers: Ticker[] = [
    mockTicker,
    { ...mockTicker, id: 2, ticker: 'MSFT', name: 'Microsoft Corporation' },
];

describe('TickerService', () => {
    let service: TickerService;
    let httpMock: HttpTestingController;

    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [TickerService, provideHttpClient(), provideHttpClientTesting()],
        });
        service = TestBed.inject(TickerService);
        httpMock = TestBed.inject(HttpTestingController);
    });

    afterEach(() => {
        httpMock.verify();
    });

    describe('getTicker', () => {
        it('GETs /ticker/:id', async () => {
            const promise = service.getTicker(1);

            const req = httpMock.expectOne(`${environment.apiUrl}/ticker/1`);
            expect(req.request.method).toBe('GET');
            req.flush(mockTicker);

            expect(await promise).toEqual(mockTicker);
        });
    });

    describe('getTickers', () => {
        it('GETs /tickers with no params when all are null', async () => {
            const promise = service.getTickers();

            const req = httpMock.expectOne(`${environment.apiUrl}/tickers`);
            expect(req.request.params.keys()).toHaveLength(0);
            req.flush(mockTickers);

            expect(await promise).toEqual(mockTickers);
        });

        it('sends search param when provided', async () => {
            const promise = service.getTickers('apple');

            const req = httpMock.expectOne((r) => r.url === `${environment.apiUrl}/tickers`);
            expect(req.request.params.get('search')).toBe('apple');
            req.flush(mockTickers);

            await promise;
        });

        it('sends limit and offset params when provided', async () => {
            const promise = service.getTickers(null, 20, 10);

            const req = httpMock.expectOne((r) => r.url === `${environment.apiUrl}/tickers`);
            expect(req.request.params.get('limit')).toBe('20');
            expect(req.request.params.get('offset')).toBe('10');
            req.flush(mockTickers);

            await promise;
        });
    });

    describe('getTickersMostUsed', () => {
        it('GETs /tickers/most-used with no params when all are null', async () => {
            const promise = service.getTickersMostUsed();

            const req = httpMock.expectOne(`${environment.apiUrl}/tickers/most-used`);
            expect(req.request.params.keys()).toHaveLength(0);
            req.flush(mockTickers);

            expect(await promise).toEqual(mockTickers);
        });

        it('sends limit param when provided', async () => {
            const promise = service.getTickersMostUsed(5);

            const req = httpMock.expectOne((r) => r.url === `${environment.apiUrl}/tickers/most-used`);
            expect(req.request.params.get('limit')).toBe('5');
            req.flush(mockTickers);

            await promise;
        });

        it('sends offset param when provided', async () => {
            const promise = service.getTickersMostUsed(null, 15);

            const req = httpMock.expectOne((r) => r.url === `${environment.apiUrl}/tickers/most-used`);
            expect(req.request.params.get('offset')).toBe('15');
            req.flush(mockTickers);

            await promise;
        });
    });
});
