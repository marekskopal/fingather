import {ChangeDetectionStrategy, Component, inject, OnInit} from '@angular/core';
import { Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { UniqueEmailValidator } from '@app/authentication/validator/UniqueEmailValidator';
import { CurrencyService } from '@app/services';
import { AuthenticationService } from '@app/services/authentication.service';
import { BaseForm } from '@app/shared/components/form/base-form';
import {SelectItem} from "@app/shared/types/select-item";

@Component({
    templateUrl: 'sign-up.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SignUpComponent extends BaseForm implements OnInit {
    private readonly route = inject(ActivatedRoute);
    private readonly router = inject(Router);
    private readonly authenticationService = inject(AuthenticationService);
    private readonly uniqueEmailValidator = inject(UniqueEmailValidator);
    private readonly currencyService = inject(CurrencyService);

    protected currencies: SelectItem<number, string>[] = [];

    public async ngOnInit(): Promise<void> {
        const currencies = await this.currencyService.getCurrencies();
        this.currencies = currencies.map((currency) => {
            return {
                key: currency.id,
                label: currency.code,
            }
        });

        this.form = this.formBuilder.group({
            name: ['', Validators.required],
            email: [
                '',
                [Validators.required, Validators.email],
                [this.uniqueEmailValidator.validate.bind(this.uniqueEmailValidator)],
                'blur'
            ],
            password: ['', [Validators.required, Validators.minLength(6)]],
            defaultCurrencyId: [this.currencies[0].key, Validators.required],
        });
    }

    public async onSubmit(): Promise<void> {
        this.$submitted.set(true);

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.$saving.set(true);
        try {
            await this.authenticationService.signUp(this.form.value);

            this.alertService.success('Registration successful', { keepAfterRouteChange: true });
            this.router.navigate(['../login'], { relativeTo: this.route });
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.$saving.set(false);
        }
    }
}
