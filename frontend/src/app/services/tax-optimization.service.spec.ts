import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { TaxOptimization } from '@app/models/tax-optimization';
import { environment } from '@environments/environment';

import { TaxOptimizationService } from './tax-optimization.service';

const mockOptimization: TaxOptimization = {
    asOfDate: '2026-05-09',
    jurisdiction: 'CzechRepublic',
    longTermHoldingDays: 1095,
    estimatedTaxRate: 0.15,
    harvestNow: [],
    holdForTaxFreeGain: [],
    lossNoLongerDeductible: [],
    alreadyTaxFree: [],
    winningShortTerm: [],
    estimatedTaxSavedByHarvestingNow: 0,
    estimatedTaxSavedByWaiting: 0,
};

describe('TaxOptimizationService', () => {
    let service: TaxOptimizationService;
    let httpMock: HttpTestingController;

    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [
                TaxOptimizationService,
                provideHttpClient(),
                provideHttpClientTesting(),
            ],
        });
        service = TestBed.inject(TaxOptimizationService);
        httpMock = TestBed.inject(HttpTestingController);
    });

    afterEach(() => {
        httpMock.verify();
    });

    describe('getTaxOptimization', () => {
        it('GETs /tax-optimization/:portfolioId and returns the result', async () => {
            const promise = service.getTaxOptimization(42);

            const req = httpMock.expectOne(`${environment.apiUrl}/tax-optimization/42`);
            expect(req.request.method).toBe('GET');
            req.flush(mockOptimization);

            expect(await promise).toEqual(mockOptimization);
        });
    });
});
