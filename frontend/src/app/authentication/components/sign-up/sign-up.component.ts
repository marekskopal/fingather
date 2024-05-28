import { ChangeDetectionStrategy, Component, OnInit } from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { UniqueEmailValidator } from '@app/authentication/validator/UniqueEmailValidator';
import { Currency } from '@app/models';
import { AlertService, CurrencyService } from '@app/services';
import { AuthenticationService } from '@app/services/authentication.service';
import { BaseForm } from '@app/shared/components/form/base-form';

@Component({
    templateUrl: 'sign-up.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SignUpComponent extends BaseForm implements OnInit {
    public currencies: Currency[];

    public constructor(
        private route: ActivatedRoute,
        private router: Router,
        private authenticationService: AuthenticationService,
        private uniqueEmailValidator: UniqueEmailValidator,
        private currencyService: CurrencyService,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {
        super(formBuilder, alertService);
    }

    public async ngOnInit(): Promise<void> {
        this.form = this.formBuilder.group({
            name: ['', Validators.required],
            email: [
                '',
                [Validators.required, Validators.email],
                [this.uniqueEmailValidator.validate.bind(this.uniqueEmailValidator)],
                'blur'
            ],
            password: ['', [Validators.required, Validators.minLength(6)]],
            defaultCurrencyId: ['', Validators.required],
        });

        this.currencies = await this.currencyService.getCurrencies();
        this.f['defaultCurrencyId'].patchValue(this.currencies[0].id);
    }

    public async onSubmit(): Promise<void> {
        this.submitted = true;

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.loading = true;
        try {
            await this.authenticationService.signUp(this.form.value);

            this.alertService.success('Registration successful', { keepAfterRouteChange: true });
            this.router.navigate(['../login'], { relativeTo: this.route });
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }

            this.loading = false;
        }
    }
}
