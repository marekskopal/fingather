import { NO_ERRORS_SCHEMA } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter, Router } from '@angular/router';
import { AlertService } from '@app/services/alert.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { CurrentUserService } from '@app/services/current-user.service';
import { provideTranslateService } from '@ngx-translate/core';

import { LoginComponent } from './login.component';

const mockCurrentUser = { isOnboardingCompleted: true };

function buildProviders(
    authOverrides: Record<string, ReturnType<typeof vi.fn>> = {},
    currentUser = mockCurrentUser,
): {
    authServiceSpy: Record<string, ReturnType<typeof vi.fn>>;
    alertServiceSpy: { error: ReturnType<typeof vi.fn>; clear: ReturnType<typeof vi.fn> };
} {
    const authServiceSpy = {
        login: vi.fn().mockResolvedValue({}),
        googleClientId: vi.fn().mockResolvedValue(''),
        googleLogin: vi.fn().mockResolvedValue({}),
        ...authOverrides,
    };
    const currentUserServiceSpy = { getCurrentUser: vi.fn().mockResolvedValue(currentUser) };
    const alertServiceSpy = { error: vi.fn(), clear: vi.fn() };

    TestBed.configureTestingModule({
        imports: [LoginComponent],
        providers: [provideTranslateService(), 
            { provide: AuthenticationService, useValue: authServiceSpy },
            { provide: CurrentUserService, useValue: currentUserServiceSpy },
            { provide: AlertService, useValue: alertServiceSpy },
            provideRouter([]),
        ],
        schemas: [NO_ERRORS_SCHEMA],
    }).compileComponents();

    return { authServiceSpy, alertServiceSpy };
}

describe('LoginComponent', () => {
    let fixture: ComponentFixture<LoginComponent>;
    let component: LoginComponent;
    let authServiceSpy: Record<string, ReturnType<typeof vi.fn>>;
    let alertServiceSpy: { error: ReturnType<typeof vi.fn>; clear: ReturnType<typeof vi.fn> };
    let router: Router;

    beforeEach(() => {
        ({ authServiceSpy, alertServiceSpy } = buildProviders());
        fixture = TestBed.createComponent(LoginComponent);
        component = fixture.componentInstance;
        router = TestBed.inject(Router);
        component.ngOnInit();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });

    it('builds a form with email and password controls', () => {
        expect(component.form.contains('email')).toBe(true);
        expect(component.form.contains('password')).toBe(true);
    });

    it('form is invalid when empty', () => {
        expect(component.form.invalid).toBe(true);
    });

    it('does not call login when form is invalid', () => {
        component.onSubmit();
        expect(authServiceSpy.login).not.toHaveBeenCalled();
    });

    it('sets submitted signal to true on submit', () => {
        component.onSubmit();
        expect(component['submitted']()).toBe(true);
    });

    it('clears alerts on submit', () => {
        component.onSubmit();
        expect(alertServiceSpy.clear).toHaveBeenCalled();
    });

    it('calls login with email and password on valid submit', async () => {
        component.form.setValue({ email: 'user@example.com', password: 'secret' });
        component.onSubmit();
        await fixture.whenStable();
        expect(authServiceSpy.login).toHaveBeenCalledWith('user@example.com', 'secret');
    });

    it('navigates to "/" after successful login when onboarding is completed', async () => {
        const navigateSpy = vi.spyOn(router, 'navigateByUrl');
        component.form.setValue({ email: 'user@example.com', password: 'secret' });
        component.onSubmit();
        await fixture.whenStable();
        expect(navigateSpy).toHaveBeenCalledWith('/');
    });

    it('resets saving to false after successful login', async () => {
        component.form.setValue({ email: 'user@example.com', password: 'secret' });
        component.onSubmit();
        await fixture.whenStable();
        expect(component['saving']()).toBe(false);
    });

    it('shows error alert when login throws an Error', async () => {
        authServiceSpy.login.mockRejectedValue(new Error('Invalid credentials'));
        component.form.setValue({ email: 'user@example.com', password: 'wrong' });
        component.onSubmit();
        await fixture.whenStable();
        expect(alertServiceSpy.error).toHaveBeenCalledWith('Invalid credentials');
    });

    it('shows error from error.error string property', async () => {
        authServiceSpy.login.mockRejectedValue({ error: 'Unauthorized' });
        component.form.setValue({ email: 'user@example.com', password: 'wrong' });
        component.onSubmit();
        await fixture.whenStable();
        expect(alertServiceSpy.error).toHaveBeenCalledWith('Unauthorized');
    });

    it('shows fallback error message for unknown error shape', async () => {
        authServiceSpy.login.mockRejectedValue('unknown error');
        component.form.setValue({ email: 'user@example.com', password: 'wrong' });
        component.onSubmit();
        await fixture.whenStable();
        expect(alertServiceSpy.error).toHaveBeenCalledWith('Login failed. Please check your credentials.');
    });

    it('resets saving to false when login rejects', async () => {
        authServiceSpy.login.mockRejectedValue(new Error('fail'));
        component.form.setValue({ email: 'user@example.com', password: 'wrong' });
        component.onSubmit();
        await fixture.whenStable();
        expect(component['saving']()).toBe(false);
    });
});

describe('LoginComponent (onboarding not completed)', () => {
    let fixture: ComponentFixture<LoginComponent>;
    let component: LoginComponent;
    let router: Router;

    beforeEach(() => {
        buildProviders({}, { isOnboardingCompleted: false });
        fixture = TestBed.createComponent(LoginComponent);
        component = fixture.componentInstance;
        router = TestBed.inject(Router);
        component.ngOnInit();
    });

    it('navigates to onboarding step-one when onboarding is not completed', async () => {
        const navigateSpy = vi.spyOn(router, 'navigateByUrl');
        component.form.setValue({ email: 'user@example.com', password: 'secret' });
        component.onSubmit();
        await fixture.whenStable();
        expect(navigateSpy).toHaveBeenCalledWith('/onboarding/step-one');
    });
});
