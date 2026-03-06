import { NO_ERRORS_SCHEMA } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { AssetsWithProperties, Currency, Portfolio } from '@app/models';
import { AssetsOrder } from '@app/models/enums/assets-order';
import { AssetService, CurrencyService, GroupWithGroupDataService, PortfolioService } from '@app/services';
import { TranslateModule } from '@ngx-translate/core';

import { ListComponent } from './list.component';

const mockPortfolio: Portfolio = { id: 1, currencyId: 1, name: 'Test Portfolio', isDefault: true };
const mockCurrency: Currency = { id: 1, code: 'USD', name: 'US Dollar', symbol: '$' };
const mockAssetsWithProperties: AssetsWithProperties = {
    openAssets: [],
    closedAssets: [],
    watchedAssets: [],
};

describe('ListComponent', () => {
    let fixture: ComponentFixture<ListComponent>;
    let component: ListComponent;
    let assetServiceSpy: { getAssetsWithProperties: ReturnType<typeof vi.fn> };
    let currencyServiceSpy: { getDefaultCurrency: ReturnType<typeof vi.fn> };
    let groupWithGroupDataServiceSpy: { getGroupsWithGroupData: ReturnType<typeof vi.fn> };
    let portfolioServiceSpy: {
        getCurrentPortfolio: ReturnType<typeof vi.fn>;
        subscribe: ReturnType<typeof vi.fn>;
    };

    beforeEach(async () => {
        assetServiceSpy = {
            getAssetsWithProperties: vi.fn().mockResolvedValue(mockAssetsWithProperties),
        };
        currencyServiceSpy = {
            getDefaultCurrency: vi.fn().mockResolvedValue(mockCurrency),
        };
        groupWithGroupDataServiceSpy = {
            getGroupsWithGroupData: vi.fn().mockResolvedValue([]),
        };
        portfolioServiceSpy = {
            getCurrentPortfolio: vi.fn().mockResolvedValue(mockPortfolio),
            getPortfolios: vi.fn().mockResolvedValue([mockPortfolio]),
            getPortfolio: vi.fn().mockResolvedValue(mockPortfolio),
            setCurrentPortfolio: vi.fn(),
            notify: vi.fn(),
            subscribe: vi.fn(),
        };

        await TestBed.configureTestingModule({
            imports: [ListComponent, TranslateModule.forRoot()],
            providers: [
                provideRouter([]),
                { provide: AssetService, useValue: assetServiceSpy },
                { provide: CurrencyService, useValue: currencyServiceSpy },
                { provide: GroupWithGroupDataService, useValue: groupWithGroupDataServiceSpy },
                { provide: PortfolioService, useValue: portfolioServiceSpy },
            ],
            schemas: [NO_ERRORS_SCHEMA],
        }).compileComponents();

        fixture = TestBed.createComponent(ListComponent);
        component = fixture.componentInstance;
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });

    describe('ngOnInit', () => {
        it('should load default currency', async () => {
            await component.ngOnInit();
            await fixture.whenStable();
            expect(currencyServiceSpy.getDefaultCurrency).toHaveBeenCalled();
            expect(component['defaultCurrency']).toEqual(mockCurrency);
        });

        it('should load assets for the current portfolio', async () => {
            await component.ngOnInit();
            await fixture.whenStable();
            expect(portfolioServiceSpy.getCurrentPortfolio).toHaveBeenCalled();
            expect(assetServiceSpy.getAssetsWithProperties).toHaveBeenCalledWith(
                mockPortfolio.id,
                AssetsOrder.TickerName,
            );
        });

        it('should set assetsWithProperties signal', async () => {
            await component.ngOnInit();
            await fixture.whenStable();
            expect(component['assetsWithProperties']()).toEqual(mockAssetsWithProperties);
        });

        it('should subscribe to portfolio changes', async () => {
            await component.ngOnInit();
            await fixture.whenStable();
            expect(portfolioServiceSpy.subscribe).toHaveBeenCalled();
        });

        it('should not load grouped assets when withGroups is false', async () => {
            await component.ngOnInit();
            await fixture.whenStable();
            expect(groupWithGroupDataServiceSpy.getGroupsWithGroupData).not.toHaveBeenCalled();
        });
    });

    describe('changeShowPerAnnum', () => {
        it('should toggle showPerAnnum from false to true', () => {
            expect(component['showPerAnnum']()).toBe(false);
            component.changeShowPerAnnum();
            expect(component['showPerAnnum']()).toBe(true);
        });

        it('should toggle showPerAnnum back to false', () => {
            component.changeShowPerAnnum();
            component.changeShowPerAnnum();
            expect(component['showPerAnnum']()).toBe(false);
        });
    });

    describe('changeOpenedAssetsOrderBy', () => {
        it('should update openedAssetsOrderBy', async () => {
            await component.ngOnInit();
            await fixture.whenStable();
            component.changeOpenedAssetsOrderBy(AssetsOrder.Value);
            expect(component.openedAssetsOrderBy).toBe(AssetsOrder.Value);
        });

        it('should reload assets with the new order', async () => {
            await component.ngOnInit();
            await fixture.whenStable();
            assetServiceSpy.getAssetsWithProperties.mockClear();

            component.changeOpenedAssetsOrderBy(AssetsOrder.Gain);
            await fixture.whenStable();

            expect(assetServiceSpy.getAssetsWithProperties).toHaveBeenCalledWith(
                mockPortfolio.id,
                AssetsOrder.Gain,
            );
        });
    });

    describe('changeWithGroups', () => {
        it('should load group data after enabling withGroups', async () => {
            await component.ngOnInit();
            await fixture.whenStable();
            groupWithGroupDataServiceSpy.getGroupsWithGroupData.mockClear();

            component.changeWithGroups();
            await fixture.whenStable();

            expect(groupWithGroupDataServiceSpy.getGroupsWithGroupData).toHaveBeenCalledWith(
                mockPortfolio.id,
                AssetsOrder.TickerName,
            );
        });

        it('should stop loading group data after disabling withGroups', async () => {
            await component.ngOnInit();
            await fixture.whenStable();
            component.changeWithGroups(); // enable
            await fixture.whenStable();
            groupWithGroupDataServiceSpy.getGroupsWithGroupData.mockClear();

            component.changeWithGroups(); // disable
            await fixture.whenStable();

            expect(groupWithGroupDataServiceSpy.getGroupsWithGroupData).not.toHaveBeenCalled();
        });

        it('should reset assetsWithProperties signal while loading', async () => {
            await component.ngOnInit();
            await fixture.whenStable();
            component.changeWithGroups();
            expect(component['assetsWithProperties']()).toBeNull();
        });
    });
});
