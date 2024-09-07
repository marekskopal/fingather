import {ChangeDetectionStrategy, Component, inject, OnInit} from '@angular/core';
import {ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute, Router, RouterLink} from '@angular/router';
import { UniqueEmailValidator } from '@app/authentication/validator/UniqueEmailValidator';
import { CurrencyService } from '@app/services';
import { AuthenticationService } from '@app/services/authentication.service';
import { BaseForm } from '@app/shared/components/form/base-form';
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import {SelectComponent} from "@app/shared/components/select/select.component";
import {SelectItem} from "@app/shared/types/select-item";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'sign-up.component.html',
    standalone: true,
    imports: [
        ReactiveFormsModule,
        TranslateModule,
        InputValidatorComponent,
        SelectComponent,
        SaveButtonComponent,
        RouterLink
    ],
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
        this.$loading.set(true);

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

        this.$loading.set(false);
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
