import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { Asset, AssetsWithProperties, AssetWithProperties } from '@app/models';
import { AssetCreate } from '@app/models/asset-create';
import { AssetsOrder } from '@app/models/enums/assets-order';
import { TickerTypeEnum } from '@app/models/enums/ticker-type-enum';
import { Market } from '@app/models/market';
import { Ticker } from '@app/models/ticker';
import { environment } from '@environments/environment';

import { AssetService } from './asset.service';

const mockMarket: Market = {
    id: 1, name: 'NYSE', acronym: 'NYSE', mic: 'XNYS',
    country: 'US', city: 'New York', timezone: 'America/New_York', currencyId: 1,
};

const mockTicker: Ticker = {
    id: 1, ticker: 'AAPL', name: 'Apple Inc.', marketId: 1, currencyId: 1,
    type: TickerTypeEnum.Stock, isin: null, logo: null, sector: null, industry: null,
    website: null, description: null, country: null, market: mockMarket,
};

const mockAsset: Asset = { id: 1, tickerId: 1, groupId: 1, price: 150, ticker: mockTicker };
const mockAssetCreate: AssetCreate = { tickerId: 1 };

const mockAssetWithProperties: AssetWithProperties = {
    id: 1, tickerId: 1, groupId: 1, price: 150, units: 10, value: 1500,
    transactionValue: 1400, transactionValueDefaultCurrency: 1400,
    averagePrice: 140, averagePriceDefaultCurrency: 140,
    gain: 100, gainDefaultCurrency: 100, realizedGain: 0, realizedGainDefaultCurrency: 0,
    gainPercentage: 7.14, gainPercentagePerAnnum: 3.5,
    dividendYield: 0, dividendYieldDefaultCurrency: 0,
    dividendYieldPercentage: 0, dividendYieldPercentagePerAnnum: 0,
    fxImpact: 0, fxImpactPercentage: 0, fxImpactPercentagePerAnnum: 0,
    return: 100, returnPercentage: 7.14, returnPercentagePerAnnum: 3.5,
    tax: 0, taxDefaultCurrency: 0, fee: 0, feeDefaultCurrency: 0,
    percentage: 100, ticker: mockTicker,
};

const mockAssetsWithProperties: AssetsWithProperties = {
    openAssets: [mockAssetWithProperties],
    closedAssets: [],
    watchedAssets: [],
};

describe('AssetService', () => {
    let service: AssetService;
    let httpMock: HttpTestingController;

    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [AssetService, provideHttpClient(), provideHttpClientTesting()],
        });
        service = TestBed.inject(AssetService);
        httpMock = TestBed.inject(HttpTestingController);
    });

    afterEach(() => {
        httpMock.verify();
    });

    describe('createAsset', () => {
        it('POSTs to /assets/:portfolioId and returns the asset', async () => {
            const promise = service.createAsset(mockAssetCreate, 1);

            const req = httpMock.expectOne(`${environment.apiUrl}/assets/1`);
            expect(req.request.method).toBe('POST');
            expect(req.request.body).toEqual(mockAssetCreate);
            req.flush(mockAsset);

            expect(await promise).toEqual(mockAsset);
        });
    });

    describe('getAssets', () => {
        it('GETs /assets/:portfolioId and returns an array', async () => {
            const promise = service.getAssets(1);

            const req = httpMock.expectOne(`${environment.apiUrl}/assets/1`);
            expect(req.request.method).toBe('GET');
            req.flush([mockAsset]);

            expect(await promise).toEqual([mockAsset]);
        });
    });

    describe('getAssetsWithProperties', () => {
        it('GETs /assets/with-properties/:portfolioId with orderBy param', async () => {
            const promise = service.getAssetsWithProperties(1, AssetsOrder.Value);

            const req = httpMock.expectOne((r) => r.url === `${environment.apiUrl}/assets/with-properties/1`);
            expect(req.request.method).toBe('GET');
            expect(req.request.params.get('orderBy')).toBe(AssetsOrder.Value);
            req.flush(mockAssetsWithProperties);

            expect(await promise).toEqual(mockAssetsWithProperties);
        });
    });

    describe('getAsset', () => {
        it('GETs /asset/:id', async () => {
            const promise = service.getAsset(1);

            const req = httpMock.expectOne(`${environment.apiUrl}/asset/1`);
            expect(req.request.method).toBe('GET');
            req.flush(mockAssetWithProperties);

            expect(await promise).toEqual(mockAssetWithProperties);
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
