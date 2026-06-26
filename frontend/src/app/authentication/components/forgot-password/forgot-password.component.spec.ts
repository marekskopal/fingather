import { NO_ERRORS_SCHEMA } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { AlertService } from '@app/services/alert.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { provideTranslateService } from '@ngx-translate/core';

import { ForgotPasswordComponent } from './forgot-password.component';

function buildProviders(overrides: Record<string, ReturnType<typeof vi.fn>> = {}): {
    authServiceSpy: { requestPasswordReset: ReturnType<typeof vi.fn> };
    alertServiceSpy: { error: ReturnType<typeof vi.fn>; clear: ReturnType<typeof vi.fn> };
} {
    const authServiceSpy = {
        requestPasswordReset: vi.fn().mockResolvedValue(undefined),
        ...overrides,
    };
    const alertServiceSpy = { error: vi.fn(), clear: vi.fn() };

    TestBed.configureTestingModule({
        imports: [ForgotPasswordComponent],
        providers: [provideTranslateService(), 
            { provide: AuthenticationService, useValue: authServiceSpy },
            { provide: AlertService, useValue: alertServiceSpy },
            provideRouter([]),
        ],
        schemas: [NO_ERRORS_SCHEMA],
    }).compileComponents();

    return { authServiceSpy, alertServiceSpy };
}

describe('ForgotPasswordComponent', () => {
    let fixture: ComponentFixture<ForgotPasswordComponent>;
    let component: ForgotPasswordComponent;
    let authServiceSpy: { requestPasswordReset: ReturnType<typeof vi.fn> };
    let alertServiceSpy: { clear: ReturnType<typeof vi.fn> };

    beforeEach(() => {
        ({ authServiceSpy, alertServiceSpy } = buildProviders());
        fixture = TestBed.createComponent(ForgotPasswordComponent);
        component = fixture.componentInstance;
        component.ngOnInit();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });

    it('builds a form with an email control', () => {
        expect(component.form.contains('email')).toBe(true);
    });

    it('form is invalid when empty', () => {
        expect(component.form.invalid).toBe(true);
    });

    it('does not call requestPasswordReset when form is invalid', () => {
        component.onSubmit();
        expect(authServiceSpy.requestPasswordReset).not.toHaveBeenCalled();
    });

    it('sets submitted signal when form is invalid', () => {
        component.onSubmit();
        expect(component['submitted']()).toBe(true);
    });

    it('clears alerts on submit', () => {
        component.onSubmit();
        expect(alertServiceSpy.clear).toHaveBeenCalled();
    });

    it('calls requestPasswordReset with email on valid submit', async () => {
        component.form.setValue({ email: 'user@example.com' });
        component.onSubmit();
        await fixture.whenStable();
        expect(authServiceSpy.requestPasswordReset).toHaveBeenCalledWith('user@example.com');
    });

    it('sets submitted$ to true after successful submission', async () => {
        component.form.setValue({ email: 'user@example.com' });
        component.onSubmit();
        await fixture.whenStable();
        expect(component['submitted$']()).toBe(true);
    });

    it('sets submitted$ to true even when requestPasswordReset rejects (hides email existence)', async () => {
        authServiceSpy.requestPasswordReset.mockRejectedValue(new Error('Not found'));
        component.form.setValue({ email: 'user@example.com' });
        component.onSubmit();
        await fixture.whenStable();
        expect(component['submitted$']()).toBe(true);
    });

    it('resets saving to false after successful submission', async () => {
        component.form.setValue({ email: 'user@example.com' });
        component.onSubmit();
        await fixture.whenStable();
        expect(component['saving']()).toBe(false);
    });

    it('resets saving to false when requestPasswordReset rejects', async () => {
        authServiceSpy.requestPasswordReset.mockRejectedValue(new Error('fail'));
        component.form.setValue({ email: 'user@example.com' });
        component.onSubmit();
        await fixture.whenStable();
        expect(component['saving']()).toBe(false);
    });
});
