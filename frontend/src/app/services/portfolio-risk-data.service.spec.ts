import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { PortfolioRiskData } from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { SamplingFrequencyEnum } from '@app/models/enums/sampling-frequency-enum';
import { environment } from '@environments/environment';

import { PortfolioRiskDataService } from './portfolio-risk-data.service';

const mockRiskData: PortfolioRiskData = {
    volatility: 18.5,
    maxDrawdown: -25.3,
    sharpeRatio: 0.87,
    beta: 1.12,
    correlationLabels: ['AAPL', 'MSFT'],
    correlationMatrix: [[1.0, 0.75], [0.75, 1.0]],
};

describe('PortfolioRiskDataService', () => {
    let service: PortfolioRiskDataService;
    let httpMock: HttpTestingController;

    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [PortfolioRiskDataService, provideHttpClient(), provideHttpClientTesting()],
        });
        service = TestBed.inject(PortfolioRiskDataService);
        httpMock = TestBed.inject(HttpTestingController);
    });

    afterEach(() => {
        httpMock.verify();
    });

    describe('getPortfolioRiskData', () => {
        it('GETs portfolio-risk-data/:portfolioId with range and samplingFrequency params', async () => {
            const promise = service.getPortfolioRiskData(1, RangeEnum.OneYear, SamplingFrequencyEnum.Weekly);

            await Promise.resolve();

            const req = httpMock.expectOne(
                (r) => r.url === `${environment.apiUrl}/portfolio-risk-data/1`,
            );
            expect(req.request.method).toBe('GET');
            expect(req.request.params.get('range')).toBe(RangeEnum.OneYear);
            expect(req.request.params.get('samplingFrequency')).toBe(SamplingFrequencyEnum.Weekly);
            req.flush(mockRiskData);

            expect(await promise).toEqual(mockRiskData);
        });

        it('includes benchmarkTickerId in query params when provided', async () => {
            const promise = service.getPortfolioRiskData(1, RangeEnum.OneYear, SamplingFrequencyEnum.Daily, 5);

            await Promise.resolve();

            const req = httpMock.expectOne(
                (r) => r.url === `${environment.apiUrl}/portfolio-risk-data/1`,
            );
            expect(req.request.params.get('benchmarkTickerId')).toBe('5');
            req.flush(mockRiskData);

            await promise;
        });

        it('includes customRangeFrom and customRangeTo in query params when provided', async () => {
            const promise = service.getPortfolioRiskData(
                1,
                RangeEnum.Custom,
                SamplingFrequencyEnum.Daily,
                null,
                '2023-01-01',
                '2023-12-31',
            );

            await Promise.resolve();

            const req = httpMock.expectOne(
                (r) => r.url === `${environment.apiUrl}/portfolio-risk-data/1`,
            );
            expect(req.request.params.get('customRangeFrom')).toBe('2023-01-01');
            expect(req.request.params.get('customRangeTo')).toBe('2023-12-31');
            req.flush(mockRiskData);

            await promise;
        });

        it('does not include benchmarkTickerId param when null', async () => {
            const promise = service.getPortfolioRiskData(1, RangeEnum.OneYear, SamplingFrequencyEnum.Daily, null);

            await Promise.resolve();

            const req = httpMock.expectOne(
                (r) => r.url === `${environment.apiUrl}/portfolio-risk-data/1`,
            );
            expect(req.request.params.has('benchmarkTickerId')).toBe(false);
            req.flush(mockRiskData);

            await promise;
        });
    });
});
