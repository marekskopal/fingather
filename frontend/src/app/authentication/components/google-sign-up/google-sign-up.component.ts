import {ChangeDetectionStrategy, Component, inject, OnInit} from '@angular/core';
import {ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute, Router, RouterLink} from '@angular/router';
import {CurrencyService, CurrentUserService} from '@app/services';
import { AuthenticationService } from '@app/services/authentication.service';
import { BaseForm } from '@app/shared/components/form/base-form';
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import {SelectComponent} from "@app/shared/components/select/select.component";
import {SelectItem} from "@app/shared/types/select-item";
import {TranslatePipe} from "@ngx-translate/core";

interface GoogleSignUpState {
    idToken: string;
    email: string;
    name: string;
}

@Component({
    templateUrl: 'google-sign-up.component.html',
    imports: [
        ReactiveFormsModule,
        TranslatePipe,
        InputValidatorComponent,
        SelectComponent,
        SaveButtonComponent,
        RouterLink,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class GoogleSignUpComponent extends BaseForm implements OnInit {
    private readonly router = inject(Router);
    private readonly authenticationService = inject(AuthenticationService);
    private readonly currencyService = inject(CurrencyService);
    private readonly currentUserService = inject(CurrentUserService);
    private readonly activatedRoute = inject(ActivatedRoute);

    protected currencies: SelectItem<number, string>[] = [];
    protected googleState: GoogleSignUpState | null = null;

    public async ngOnInit(): Promise<void> {
        this.googleState = this.activatedRoute.snapshot.queryParams as GoogleSignUpState | null;

        if (!this.googleState?.idToken) {
            this.router.navigate(['/authentication/login']);
            return;
        }

        this.loading.set(true);

        const currencies = await this.currencyService.getCurrencies();
        this.currencies = currencies.map((currency) => {
            return {
                key: currency.id,
                label: currency.code,
            }
        });

        this.form = this.formBuilder.group({
            defaultCurrencyId: [this.currencies[0].key, Validators.required],
        });

        this.loading.set(false);
    }

    public async onSubmit(): Promise<void> {
        this.submitted.set(true);

        this.alertService.clear();

        if (this.form.invalid || !this.googleState?.idToken) {
            return;
        }

        this.saving.set(true);
        try {
            await this.authenticationService.googleLogin(
                this.googleState.idToken,
                this.f['defaultCurrencyId'].value,
            );

            const currentUser = await this.currentUserService.getCurrentUser();
            const returnUrl = currentUser.isOnboardingCompleted ? '/' : '/onboarding/step-one';

            this.router.navigateByUrl(returnUrl);
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.saving.set(false);
        }
    }
}
