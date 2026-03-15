import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { StrategyRebalancing, StrategyRebalancingRequest } from '@app/models';
import { environment } from '@environments/environment';

import { StrategyRebalancingService } from './strategy-rebalancing.service';

const mockRequest: StrategyRebalancingRequest = {
    cashToInvest: '500',
    cashCurrencyId: 1,
    allowSelling: false,
};

const mockRebalancing: StrategyRebalancing = {
    id: 1,
    name: 'My Strategy',
    portfolioValue: '1000',
    cashToInvest: '500',
    items: [
        {
            name: 'Apple Inc.',
            color: null,
            assetId: 1,
            groupId: null,
            isOthers: false,
            targetPercentage: 60,
            actualPercentage: 40,
            differencePercentage: -20,
            currentValue: '400',
            targetValue: '900',
            suggestedTradeValue: '500',
            suggestedTradeUnits: '2.5',
            currentPrice: '200',
        },
        {
            name: 'Others',
            color: null,
            assetId: null,
            groupId: null,
            isOthers: true,
            targetPercentage: 40,
            actualPercentage: 60,
            differencePercentage: 20,
            currentValue: '600',
            targetValue: '600',
            suggestedTradeValue: '0',
            suggestedTradeUnits: null,
            currentPrice: null,
        },
    ],
};

describe('StrategyRebalancingService', () => {
    let service: StrategyRebalancingService;
    let httpMock: HttpTestingController;

    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [StrategyRebalancingService, provideHttpClient(), provideHttpClientTesting()],
        });
        service = TestBed.inject(StrategyRebalancingService);
        httpMock = TestBed.inject(HttpTestingController);
    });

    afterEach(() => {
        httpMock.verify();
    });

    describe('calculate', () => {
        it('POSTs to /strategy-rebalancing/:id and returns the rebalancing result', async () => {
            const promise = service.calculate(1, mockRequest);

            const req = httpMock.expectOne(`${environment.apiUrl}/strategy-rebalancing/1`);
            expect(req.request.method).toBe('POST');
            expect(req.request.body).toEqual(mockRequest);
            req.flush(mockRebalancing);

            expect(await promise).toEqual(mockRebalancing);
        });

        it('sends the correct strategyId in the URL', async () => {
            const promise = service.calculate(42, mockRequest);

            const req = httpMock.expectOne(`${environment.apiUrl}/strategy-rebalancing/42`);
            req.flush(mockRebalancing);

            await promise;
        });

        it('includes cashCurrencyId null in the request body', async () => {
            const requestWithNullCurrency: StrategyRebalancingRequest = {
                ...mockRequest,
                cashCurrencyId: null,
            };

            const promise = service.calculate(1, requestWithNullCurrency);

            const req = httpMock.expectOne(`${environment.apiUrl}/strategy-rebalancing/1`);
            expect(req.request.body.cashCurrencyId).toBeNull();
            req.flush(mockRebalancing);

            await promise;
        });

        it('passes allowSelling true in the request body', async () => {
            const promise = service.calculate(1, { ...mockRequest, allowSelling: true });

            const req = httpMock.expectOne(`${environment.apiUrl}/strategy-rebalancing/1`);
            expect(req.request.body.allowSelling).toBe(true);
            req.flush(mockRebalancing);

            await promise;
        });
    });
});
