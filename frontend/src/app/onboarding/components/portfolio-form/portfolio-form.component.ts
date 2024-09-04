import {ChangeDetectionStrategy, Component, inject, OnInit} from '@angular/core';
import { Validators } from '@angular/forms';
import { Portfolio } from '@app/models';
import {
    CurrencyService,
    PortfolioService
} from '@app/services';
import { BaseForm } from '@app/shared/components/form/base-form';
import {SelectItem} from "@app/shared/types/select-item";

@Component({
    templateUrl: 'portfolio-form.component.html',
    selector: 'fingather-onboarding-portfolio-form',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PortfolioFormComponent extends BaseForm implements OnInit {
    private readonly portfolioService = inject(PortfolioService);
    private readonly currencyService = inject(CurrencyService);

    protected portfolio: Portfolio;
    protected currencies: SelectItem<number, string>[] = [];

    public async ngOnInit(): Promise<void> {
        this.portfolio = await this.portfolioService.getCurrentPortfolio();

        const currencies = await this.currencyService.getCurrencies();
        this.currencies = currencies.map((currency) => {
            return {
                key: currency.id,
                label: currency.code,
            }
        });

        this.form = this.formBuilder.group({
            name: [this.portfolio.name, Validators.required],
            currencyId: [this.portfolio.currencyId, Validators.required],
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
            this.updatePortfolio(this.portfolio.id);
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.$saving.set(false);
        }
    }

    private async updatePortfolio(portfolioId: number): Promise<void> {
        await this.portfolioService.updatePortfolio(portfolioId, this.form.value);

        this.alertService.success('Update successful', { keepAfterRouteChange: true });
        this.portfolioService.notify();
    }
}
