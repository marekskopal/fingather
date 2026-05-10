import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { CostBasisComparison } from '@app/models/cost-basis-comparison';
import { environment } from '@environments/environment';

import { CostBasisComparisonService } from './cost-basis-comparison.service';

const mockComparison: CostBasisComparison = {
    year: 2024,
    configuredMethod: 'Fifo',
    optimalMethod: 'AverageCost',
    estimatedTaxRate: 0.15,
    annualGainExemption: null,
    annualGrossProceedsExemption: '100000',
    rows: [],
};

describe('CostBasisComparisonService', () => {
    let service: CostBasisComparisonService;
    let httpMock: HttpTestingController;

    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [
                CostBasisComparisonService,
                provideHttpClient(),
                provideHttpClientTesting(),
            ],
        });
        service = TestBed.inject(CostBasisComparisonService);
        httpMock = TestBed.inject(HttpTestingController);
    });

    afterEach(() => {
        httpMock.verify();
    });

    describe('getCostBasisComparison', () => {
        it('GETs the year-scoped comparison endpoint', async () => {
            const promise = service.getCostBasisComparison(7, 2024);

            const req = httpMock.expectOne(`${environment.apiUrl}/tax-report/7/2024/cost-basis-comparison`);
            expect(req.request.method).toBe('GET');
            req.flush(mockComparison);

            expect(await promise).toEqual(mockComparison);
        });
    });
});
