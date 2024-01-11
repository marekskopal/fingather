import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { first } from 'rxjs/operators';

import {AlertService, CurrencyService} from '@app/services';
import {BaseForm} from "@app/shared/components/form/base-form";
import {UniqueEmailValidator} from "@app/authentication/validator/UniqueEmailValidator";
import {Currency} from "@app/models";
import {AuthenticationService} from "@app/services/authentication.service";

@Component({ templateUrl: 'sign-up.component.html' })
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
            email: ['', [Validators.required, Validators.email], [this.uniqueEmailValidator.validate.bind(this.uniqueEmailValidator)], 'blur'],
            password: ['', [Validators.required, Validators.minLength(6)]],
            defaultCurrencyId: ['', Validators.required],
        });

        this.currencyService.getCurrencies()
            .pipe(first())
            .subscribe((currencies: Currency[]) => {
                this.currencies = currencies;
                this.f['defaultCurrencyId'].patchValue(currencies[0].id);
            });
    }

    public onSubmit(): void {
        this.submitted = true;

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.loading = true;
        this.authenticationService.signUp(this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Registration successful', { keepAfterRouteChange: true });
                    this.router.navigate(['../login'], { relativeTo: this.route });
                },
                error: error => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }
}
