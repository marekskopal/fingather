import { NO_ERRORS_SCHEMA } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { Router } from '@angular/router';
import { UniqueEmailValidator } from '@app/authentication/validator/UniqueEmailValidator';
import { Currency } from '@app/models';
import { AlertService } from '@app/services/alert.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { CurrencyService } from '@app/services/currency.service';
import { CurrentUserService } from '@app/services/current-user.service';
import { provideTranslateService } from '@ngx-translate/core';

import { SignUpComponent } from './sign-up.component';

const mockCurrencies: Currency[] = [
    { id: 1, code: 'USD', name: 'US Dollar', symbol: '$' },
    { id: 2, code: 'EUR', name: 'Euro', symbol: '€' },
];

const mockCurrentUser = { isOnboardingCompleted: false };

function buildProviders(overrides: Record<string, ReturnType<typeof vi.fn>> = {}): {
    authServiceSpy: { signUp: ReturnType<typeof vi.fn> };
    currencyServiceSpy: { getCurrencies: ReturnType<typeof vi.fn> };
    currentUserServiceSpy: { getCurrentUser: ReturnType<typeof vi.fn> };
    alertServiceSpy: { error: ReturnType<typeof vi.fn>; clear: ReturnType<typeof vi.fn> };
    routerSpy: { navigateByUrl: ReturnType<typeof vi.fn> };
} {
    const authServiceSpy = {
        signUp: vi.fn().mockResolvedValue({}),
        ...overrides,
    };
    const currencyServiceSpy = { getCurrencies: vi.fn().mockResolvedValue(mockCurrencies) };
    const currentUserServiceSpy = { getCurrentUser: vi.fn().mockResolvedValue(mockCurrentUser) };
    const alertServiceSpy = { error: vi.fn(), clear: vi.fn() };
    const routerSpy = { navigateByUrl: vi.fn() };
    const uniqueEmailValidatorSpy = { validate: vi.fn().mockResolvedValue(null) };

    TestBed.configureTestingModule({
        imports: [SignUpComponent],
        providers: [provideTranslateService(), 
            { provide: AuthenticationService, useValue: authServiceSpy },
            { provide: CurrencyService, useValue: currencyServiceSpy },
            { provide: CurrentUserService, useValue: currentUserServiceSpy },
            { provide: AlertService, useValue: alertServiceSpy },
            { provide: Router, useValue: routerSpy },
            { provide: UniqueEmailValidator, useValue: uniqueEmailValidatorSpy },
        ],
        schemas: [NO_ERRORS_SCHEMA],
    }).compileComponents();

    return { authServiceSpy, currencyServiceSpy, currentUserServiceSpy, alertServiceSpy, routerSpy };
}

describe('SignUpComponent', () => {
    let fixture: ComponentFixture<SignUpComponent>;
    let component: SignUpComponent;
    let authServiceSpy: { signUp: ReturnType<typeof vi.fn> };
    let currencyServiceSpy: { getCurrencies: ReturnType<typeof vi.fn> };
    let alertServiceSpy: { error: ReturnType<typeof vi.fn>; clear: ReturnType<typeof vi.fn> };
    let routerSpy: { navigateByUrl: ReturnType<typeof vi.fn> };

    beforeEach(async () => {
        ({ authServiceSpy, currencyServiceSpy, alertServiceSpy, routerSpy } = buildProviders());
        fixture = TestBed.createComponent(SignUpComponent);
        component = fixture.componentInstance;
        await component.ngOnInit();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });

    it('loads currencies on init', () => {
        expect(currencyServiceSpy.getCurrencies).toHaveBeenCalledOnce();
    });

    it('populates currencies list from service', () => {
        expect(component['currencies']).toHaveLength(2);
        expect(component['currencies'][0].label).toBe('USD');
    });

    it('sets loading to false after init', () => {
        expect(component['loading']()).toBe(false);
    });

    it('builds a form with required controls', () => {
        expect(component.form.contains('name')).toBe(true);
        expect(component.form.contains('email')).toBe(true);
        expect(component.form.contains('password')).toBe(true);
        expect(component.form.contains('defaultCurrencyId')).toBe(true);
    });

    it('pre-selects the first currency as defaultCurrencyId', () => {
        expect(component.form.get('defaultCurrencyId')?.value).toBe(1);
    });

    it('does not call signUp when form is invalid', () => {
        component.onSubmit();
        expect(authServiceSpy.signUp).not.toHaveBeenCalled();
    });

    it('sets submitted signal on submit', () => {
        component.onSubmit();
        expect(component['submitted']()).toBe(true);
    });

    it('calls signUp with form value on valid submit', async () => {
        component.form.patchValue({ name: 'Alice', email: 'alice@example.com', password: 'Password1!' });
        component.form.get('email')?.setErrors(null);
        component.form.get('password')?.setErrors(null);
        component.onSubmit();
        await fixture.whenStable();
        expect(authServiceSpy.signUp).toHaveBeenCalledOnce();
    });

    it('navigates to onboarding when onboarding is not completed', async () => {
        component.form.patchValue({ name: 'Alice', email: 'alice@example.com', password: 'Password1!' });
        component.form.get('email')?.setErrors(null);
        component.form.get('password')?.setErrors(null);
        component.onSubmit();
        await fixture.whenStable();
        expect(routerSpy.navigateByUrl).toHaveBeenCalledWith('/onboarding/step-one');
    });

    it('resets saving to false after successful sign-up', async () => {
        component.form.patchValue({ name: 'Alice', email: 'alice@example.com', password: 'Password1!' });
        component.form.get('email')?.setErrors(null);
        component.form.get('password')?.setErrors(null);
        component.onSubmit();
        await fixture.whenStable();
        expect(component['saving']()).toBe(false);
    });

    it('shows error alert when signUp rejects', async () => {
        authServiceSpy.signUp.mockRejectedValue(new Error('Email already taken'));
        component.form.patchValue({ name: 'Alice', email: 'alice@example.com', password: 'Password1!' });
        component.form.get('email')?.setErrors(null);
        component.form.get('password')?.setErrors(null);
        component.onSubmit();
        await fixture.whenStable();
        expect(alertServiceSpy.error).toHaveBeenCalledWith('Email already taken');
    });

    it('resets saving to false when signUp rejects', async () => {
        authServiceSpy.signUp.mockRejectedValue(new Error('fail'));
        component.form.patchValue({ name: 'Alice', email: 'alice@example.com', password: 'Password1!' });
        component.form.get('email')?.setErrors(null);
        component.form.get('password')?.setErrors(null);
        component.onSubmit();
        await fixture.whenStable();
        expect(component['saving']()).toBe(false);
    });
});
