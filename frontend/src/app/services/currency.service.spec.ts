import { provideHttpClient, withInterceptorsFromDi } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { Currency } from '@app/models';
import { Portfolio } from '@app/models/portfolio';
import { PortfolioService } from '@app/services/portfolio.service';
import { environment } from '@environments/environment';

import { CurrencyService } from './currency.service';

const mockCurrencies: Currency[] = [
    { id: 1, code: 'USD', name: 'US Dollar', symbol: '$' },
    { id: 2, code: 'EUR', name: 'Euro', symbol: '€' },
];

describe('CurrencyService', () => {
    let service: CurrencyService;
    let httpMock: HttpTestingController;
    let portfolioServiceSpy: { getCurrentPortfolio: ReturnType<typeof vi.fn> };

    beforeEach(() => {
        portfolioServiceSpy = { getCurrentPortfolio: vi.fn() };

        TestBed.configureTestingModule({
            providers: [
                CurrencyService,
                provideHttpClient(withInterceptorsFromDi()),
                provideHttpClientTesting(),
                { provide: PortfolioService, useValue: portfolioServiceSpy },
            ],
        });

        service = TestBed.inject(CurrencyService);
        httpMock = TestBed.inject(HttpTestingController);
    });

    afterEach(() => {
        httpMock.verify();
    });

    describe('getCurrencies', () => {
        it('makes a GET request and returns an array of currencies', async () => {
            const promise = service.getCurrencies();

            const req = httpMock.expectOne(`${environment.apiUrl}/currency`);
            expect(req.request.method).toBe('GET');
            req.flush(mockCurrencies);

            const result = await promise;
            expect(result).toEqual(mockCurrencies);
        });
    });

    describe('getCurrenciesMap', () => {
        it('builds a Map keyed by currency id', async () => {
            const promise = service.getCurrenciesMap();

            const req = httpMock.expectOne(`${environment.apiUrl}/currency`);
            req.flush(mockCurrencies);

            const map = await promise;
            expect(map.get(1)).toEqual(mockCurrencies[0]);
            expect(map.get(2)).toEqual(mockCurrencies[1]);
        });

        it('returns the cached map on second call without a second HTTP request', async () => {
            const promise1 = service.getCurrenciesMap();
            httpMock.expectOne(`${environment.apiUrl}/currency`).flush(mockCurrencies);
            await promise1;

            const map2 = await service.getCurrenciesMap();
            httpMock.expectNone(`${environment.apiUrl}/currency`);
            expect(map2.get(1)).toEqual(mockCurrencies[0]);
        });
    });

    describe('getDefaultCurrency', () => {
        it('throws when the portfolio currency is not found in the map', async () => {
            const mockPortfolio: Portfolio = { id: 1, currencyId: 99, name: 'Test', isDefault: true };
            portfolioServiceSpy.getCurrentPortfolio.mockResolvedValue(mockPortfolio);

            const promise = service.getDefaultCurrency();
            // allow portfolioService.getCurrentPortfolio() promise to resolve
            // so that getCurrenciesMap() is called and the HTTP request is initiated
            await Promise.resolve();

            httpMock.expectOne(`${environment.apiUrl}/currency`).flush(mockCurrencies);

            await expect(promise).rejects.toThrow('Default currency not exists.');
        });

        it('returns the currency matching the portfolio currencyId', async () => {
            const mockPortfolio: Portfolio = { id: 1, currencyId: 2, name: 'Test', isDefault: true };
            portfolioServiceSpy.getCurrentPortfolio.mockResolvedValue(mockPortfolio);

            const promise = service.getDefaultCurrency();
            // allow portfolioService.getCurrentPortfolio() promise to resolve
            await Promise.resolve();

            httpMock.expectOne(`${environment.apiUrl}/currency`).flush(mockCurrencies);

            const result = await promise;
            expect(result).toEqual(mockCurrencies[1]);
        });
    });
});
