import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import {
    PortfolioTaxSettings,
    PortfolioTaxSettingsUpdate,
} from '@app/models/portfolio-tax-settings';
import { environment } from '@environments/environment';

import { PortfolioTaxSettingsService } from './portfolio-tax-settings.service';

const mockSettings: PortfolioTaxSettings = {
    portfolioId: 1,
    taxJurisdiction: 'CzechRepublic',
    costBasisMethod: 'Fifo',
    estimatedTaxRate: '0.15',
    longTermHoldingDays: 1095,
    defaultEstimatedTaxRate: '0.15',
    allowedCostBasisMethods: ['Fifo', 'AverageCost'],
};

describe('PortfolioTaxSettingsService', () => {
    let service: PortfolioTaxSettingsService;
    let httpMock: HttpTestingController;

    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [
                PortfolioTaxSettingsService,
                provideHttpClient(),
                provideHttpClientTesting(),
            ],
        });
        service = TestBed.inject(PortfolioTaxSettingsService);
        httpMock = TestBed.inject(HttpTestingController);
    });

    afterEach(() => {
        httpMock.verify();
    });

    describe('getTaxSettings', () => {
        it('GETs /portfolio/:id/tax-settings', async () => {
            const promise = service.getTaxSettings(1);

            const req = httpMock.expectOne(`${environment.apiUrl}/portfolio/1/tax-settings`);
            expect(req.request.method).toBe('GET');
            req.flush(mockSettings);

            expect(await promise).toEqual(mockSettings);
        });
    });

    describe('updateTaxSettings', () => {
        it('PUTs to /portfolio/:id/tax-settings with the update payload', async () => {
            const update: PortfolioTaxSettingsUpdate = {
                taxJurisdiction: 'Generic',
                costBasisMethod: 'Lifo',
                estimatedTaxRate: null,
            };

            const promise = service.updateTaxSettings(1, update);

            const req = httpMock.expectOne(`${environment.apiUrl}/portfolio/1/tax-settings`);
            expect(req.request.method).toBe('PUT');
            expect(req.request.body).toEqual(update);
            req.flush({ ...mockSettings, taxJurisdiction: 'Generic', costBasisMethod: 'Lifo', estimatedTaxRate: null });

            const result = await promise;
            expect(result.taxJurisdiction).toBe('Generic');
            expect(result.costBasisMethod).toBe('Lifo');
        });
    });
});
