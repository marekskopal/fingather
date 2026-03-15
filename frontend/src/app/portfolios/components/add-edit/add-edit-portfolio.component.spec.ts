import { NO_ERRORS_SCHEMA } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ActivatedRoute, Router } from '@angular/router';
import { Currency, Portfolio } from '@app/models';
import { AlertService } from '@app/services/alert.service';
import { CurrencyService } from '@app/services/currency.service';
import { PortfolioService } from '@app/services/portfolio.service';
import { TranslateModule } from '@ngx-translate/core';

import { AddEditPortfolioComponent } from './add-edit-portfolio.component';

const mockCurrencies: Currency[] = [
    { id: 1, code: 'USD', name: 'US Dollar', symbol: '$' },
    { id: 2, code: 'EUR', name: 'Euro', symbol: '€' },
];

const mockPortfolio: Portfolio = { id: 5, name: 'Test Portfolio', currencyId: 1, isDefault: false };

function buildProviders(
    routeParams: Record<string, string>,
    portfolioServiceOverrides: Record<string, ReturnType<typeof vi.fn>> = {},
): {
    portfolioServiceSpy: Record<string, ReturnType<typeof vi.fn>>;
    currencyServiceSpy: { getCurrencies: ReturnType<typeof vi.fn> };
    alertServiceSpy: { success: ReturnType<typeof vi.fn>; error: ReturnType<typeof vi.fn>; clear: ReturnType<typeof vi.fn> };
    routerSpy: { navigate: ReturnType<typeof vi.fn> };
} {
    const portfolioServiceSpy = {
        createPortfolio: vi.fn().mockResolvedValue(mockPortfolio),
        updatePortfolio: vi.fn().mockResolvedValue(mockPortfolio),
        getPortfolio: vi.fn().mockResolvedValue(mockPortfolio),
        getPortfolios: vi.fn().mockResolvedValue([mockPortfolio]),
        getCurrentPortfolio: vi.fn().mockResolvedValue(mockPortfolio),
        cleanCurrentPortfolio: vi.fn(),
        setCurrentPortfolio: vi.fn(),
        getDefaultPortfolio: vi.fn().mockResolvedValue(mockPortfolio),
        notify: vi.fn(),
        subscribe: vi.fn(),
        ...portfolioServiceOverrides,
    };
    const currencyServiceSpy = { getCurrencies: vi.fn().mockResolvedValue(mockCurrencies) };
    const alertServiceSpy = { success: vi.fn(), error: vi.fn(), clear: vi.fn() };
    const routerSpy = { navigate: vi.fn() };

    TestBed.configureTestingModule({
        imports: [AddEditPortfolioComponent, TranslateModule.forRoot()],
        providers: [
            { provide: PortfolioService, useValue: portfolioServiceSpy },
            { provide: CurrencyService, useValue: currencyServiceSpy },
            { provide: AlertService, useValue: alertServiceSpy },
            { provide: Router, useValue: routerSpy },
            { provide: ActivatedRoute, useValue: { snapshot: { params: routeParams } } },
        ],
        schemas: [NO_ERRORS_SCHEMA],
    }).compileComponents();

    return { portfolioServiceSpy, currencyServiceSpy, alertServiceSpy, routerSpy };
}

describe('AddEditPortfolioComponent (create mode)', () => {
    let fixture: ComponentFixture<AddEditPortfolioComponent>;
    let component: AddEditPortfolioComponent;
    let portfolioServiceSpy: Record<string, ReturnType<typeof vi.fn>>;
    let currencyServiceSpy: { getCurrencies: ReturnType<typeof vi.fn> };
    let alertServiceSpy: { success: ReturnType<typeof vi.fn>; error: ReturnType<typeof vi.fn>; clear: ReturnType<typeof vi.fn> };
    let routerSpy: { navigate: ReturnType<typeof vi.fn> };

    beforeEach(async () => {
        ({ portfolioServiceSpy, currencyServiceSpy, alertServiceSpy, routerSpy } =
            buildProviders({}));
        fixture = TestBed.createComponent(AddEditPortfolioComponent);
        component = fixture.componentInstance;
        await component.ngOnInit();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });

    it('loads currencies on init', () => {
        expect(currencyServiceSpy.getCurrencies).toHaveBeenCalledOnce();
    });

    it('does not load a portfolio on init when no id is in the route', () => {
        expect(portfolioServiceSpy.getPortfolio).not.toHaveBeenCalled();
    });

    it('does not call createPortfolio when form is invalid', () => {
        component.form.get('name')?.setValue('');
        component.onSubmit();
        expect(portfolioServiceSpy.createPortfolio).not.toHaveBeenCalled();
    });

    it('calls createPortfolio and navigates on valid submit', async () => {
        component.onSubmit();
        await fixture.whenStable();
        expect(portfolioServiceSpy.createPortfolio).toHaveBeenCalledOnce();
        expect(routerSpy.navigate).toHaveBeenCalledWith(['../'], expect.any(Object));
    });

    it('shows success alert after successful creation', async () => {
        component.onSubmit();
        await fixture.whenStable();
        expect(alertServiceSpy.success).toHaveBeenCalled();
    });

    it('resets saving to false after successful creation', async () => {
        component.onSubmit();
        await fixture.whenStable();
        expect(component['saving']()).toBe(false);
    });

    it('shows error alert when createPortfolio rejects', async () => {
        portfolioServiceSpy.createPortfolio.mockRejectedValue(new Error('Network error'));
        component.onSubmit();
        await fixture.whenStable();
        expect(alertServiceSpy.error).toHaveBeenCalledWith('Network error');
    });

    it('resets saving to false when createPortfolio rejects', async () => {
        portfolioServiceSpy.createPortfolio.mockRejectedValue(new Error('fail'));
        component.onSubmit();
        await fixture.whenStable();
        expect(component['saving']()).toBe(false);
    });
});

describe('AddEditPortfolioComponent (edit mode)', () => {
    let fixture: ComponentFixture<AddEditPortfolioComponent>;
    let component: AddEditPortfolioComponent;
    let portfolioServiceSpy: Record<string, ReturnType<typeof vi.fn>>;
    let alertServiceSpy: { success: ReturnType<typeof vi.fn>; error: ReturnType<typeof vi.fn>; clear: ReturnType<typeof vi.fn> };
    let routerSpy: { navigate: ReturnType<typeof vi.fn> };

    beforeEach(async () => {
        ({ portfolioServiceSpy, alertServiceSpy, routerSpy } = buildProviders({ id: '5' }));
        fixture = TestBed.createComponent(AddEditPortfolioComponent);
        component = fixture.componentInstance;
        await component.ngOnInit();
    });

    it('loads the existing portfolio on init', () => {
        expect(portfolioServiceSpy.getPortfolio).toHaveBeenCalledWith(5);
    });

    it('calls updatePortfolio and navigates on valid submit', async () => {
        component.onSubmit();
        await fixture.whenStable();
        expect(portfolioServiceSpy.updatePortfolio).toHaveBeenCalledWith(5, expect.any(Object));
        expect(routerSpy.navigate).toHaveBeenCalled();
    });

    it('shows success alert after successful update', async () => {
        component.onSubmit();
        await fixture.whenStable();
        expect(alertServiceSpy.success).toHaveBeenCalled();
    });

    it('shows error alert when updatePortfolio rejects', async () => {
        portfolioServiceSpy.updatePortfolio.mockRejectedValue(new Error('Update failed'));
        component.onSubmit();
        await fixture.whenStable();
        expect(alertServiceSpy.error).toHaveBeenCalledWith('Update failed');
    });

    it('resets saving to false when updatePortfolio rejects', async () => {
        portfolioServiceSpy.updatePortfolio.mockRejectedValue(new Error('fail'));
        component.onSubmit();
        await fixture.whenStable();
        expect(component['saving']()).toBe(false);
    });
});
