import { NO_ERRORS_SCHEMA } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { Currency, Portfolio, PortfolioData } from '@app/models';
import { CurrencyService, PortfolioDataService, PortfolioService } from '@app/services';
import { provideTranslateService } from '@ngx-translate/core';

import { PortfolioTotalComponent } from './portfolio-total.component';

const mockCurrency: Currency = {
    id: 1,
    code: 'USD',
    name: 'US Dollar',
    symbol: '$',
};

const mockPortfolio: Portfolio = {
    id: 1,
    currencyId: 1,
    name: 'Test Portfolio',
    isDefault: true,
};

const mockPortfolioData: PortfolioData = {
    id: 1,
    date: '2024-01-01',
    value: '1000.00',
    transactionValue: '800.00',
    gain: 200,
    gainPercentage: 25,
    gainPercentagePerAnnum: 12.5,
    realizedGain: 0,
    dividendYield: 0,
    dividendYieldPercentage: 0,
    dividendYieldPercentagePerAnnum: 0,
    fxImpact: 0,
    fxImpactPercentage: 0,
    fxImpactPercentagePerAnnum: 0,
    return: 200,
    returnPercentage: 25,
    returnPercentagePerAnnum: 12.5,
    tax: '0.00',
    fee: '0.00',
    twrPercentage: 22.5,
    twrPercentagePerAnnum: 11.25,
    mwrPercentage: 18.3,
};

describe('PortfolioTotalComponent', () => {
    let fixture: ComponentFixture<PortfolioTotalComponent>;
    let component: PortfolioTotalComponent;
    let portfolioDataServiceSpy: { getPortfolioData: ReturnType<typeof vi.fn>; getPortfolioDataRange: ReturnType<typeof vi.fn> };
    let currencyServiceSpy: { getDefaultCurrency: ReturnType<typeof vi.fn> };
    let portfolioServiceSpy: { getCurrentPortfolio: ReturnType<typeof vi.fn>; subscribe: ReturnType<typeof vi.fn> };

    beforeEach(async () => {
        portfolioDataServiceSpy = {
            getPortfolioData: vi.fn().mockResolvedValue(mockPortfolioData),
            getPortfolioDataRange: vi.fn().mockResolvedValue([]),
        };
        currencyServiceSpy = { getDefaultCurrency: vi.fn().mockResolvedValue(mockCurrency) };
        portfolioServiceSpy = {
            getCurrentPortfolio: vi.fn().mockResolvedValue(mockPortfolio),
            subscribe: vi.fn(),
        };

        await TestBed.configureTestingModule({
            imports: [PortfolioTotalComponent],
            providers: [provideTranslateService(), 
                { provide: PortfolioDataService, useValue: portfolioDataServiceSpy },
                { provide: CurrencyService, useValue: currencyServiceSpy },
                { provide: PortfolioService, useValue: portfolioServiceSpy },
            ],
            schemas: [NO_ERRORS_SCHEMA],
        }).compileComponents();

        fixture = TestBed.createComponent(PortfolioTotalComponent);
        component = fixture.componentInstance;
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });

    describe('ngOnInit()', () => {
        it('should call currencyService.getDefaultCurrency()', async () => {
            await component.ngOnInit();
            expect(currencyServiceSpy.getDefaultCurrency).toHaveBeenCalled();
        });

        it('should subscribe to portfolio service changes', async () => {
            await component.ngOnInit();
            expect(portfolioServiceSpy.subscribe).toHaveBeenCalled();
        });

        it('should call portfolioService.getCurrentPortfolio() when no input portfolio', async () => {
            await component.ngOnInit();
            await Promise.resolve();
            expect(portfolioServiceSpy.getCurrentPortfolio).toHaveBeenCalled();
        });

        it('should set portfolioData signal after init', async () => {
            await component.ngOnInit();
            await Promise.resolve();
            await Promise.resolve();
            expect(component['portfolioData']()).toEqual(mockPortfolioData);
        });
    });

    describe('refreshPortfolioData()', () => {
        it('should set portfolioData to null before fetching', async () => {
            component['portfolioData'].set(mockPortfolioData);

            const refreshPromise = component.refreshPortfolioData();
            expect(component['portfolioData']()).toBeNull();

            await refreshPromise;
        });

        it('should call getPortfolioData with portfolio id', async () => {
            await component.refreshPortfolioData();
            expect(portfolioDataServiceSpy.getPortfolioData).toHaveBeenCalledWith(mockPortfolio.id);
        });

        it('should set portfolioData signal with fetched result', async () => {
            await component.refreshPortfolioData();
            expect(component['portfolioData']()).toEqual(mockPortfolioData);
        });
    });

    describe('with input portfolio', () => {
        beforeEach(() => {
            fixture.componentRef.setInput('portfolio', mockPortfolio);
        });

        it('should not call getCurrentPortfolio() when portfolio input is set', async () => {
            await component.refreshPortfolioData();
            expect(portfolioServiceSpy.getCurrentPortfolio).not.toHaveBeenCalled();
        });

        it('should call getPortfolioData with input portfolio id', async () => {
            await component.refreshPortfolioData();
            expect(portfolioDataServiceSpy.getPortfolioData).toHaveBeenCalledWith(mockPortfolio.id);
        });
    });
});
