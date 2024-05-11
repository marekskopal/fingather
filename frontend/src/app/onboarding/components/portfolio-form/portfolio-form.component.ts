import { Component, OnInit } from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { Currency, Portfolio } from '@app/models';
import {
    AlertService,
    CurrencyService,
    PortfolioService
} from '@app/services';
import { BaseForm } from '@app/shared/components/form/base-form';

@Component({
    templateUrl: 'portfolio-form.component.html',
    selector: 'fingather-onboarding-portfolio-form'
})
export class PortfolioFormComponent extends BaseForm implements OnInit {
    protected portfolio: Portfolio;
    protected currencies: Currency[];

    public constructor(
        private readonly portfolioService: PortfolioService,
        private readonly currencyService: CurrencyService,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {
        super(formBuilder, alertService);
    }

    public async ngOnInit(): Promise<void> {
        this.portfolio = await this.portfolioService.getCurrentPortfolio();

        this.currencies = await this.currencyService.getCurrencies();

        this.form = this.formBuilder.group({
            name: [this.portfolio.name, Validators.required],
            currencyId: [this.portfolio.currencyId, Validators.required],
        });
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

        this.updatePortfolio(this.portfolio.id);
    }

    private async updatePortfolio(portfolioId: number): Promise<void> {
        try {
            await this.portfolioService.updatePortfolio(portfolioId, this.form.value);

            this.alertService.success('Update successful', { keepAfterRouteChange: true });
            this.portfolioService.notify();
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
            this.loading = false;
        }
    }
}
