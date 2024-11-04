import {ChangeDetectionStrategy, Component, inject, OnInit} from '@angular/core';
import {ReactiveFormsModule, Validators} from "@angular/forms";
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import {Portfolio} from "@app/models";
import {BaseOnboardingComponent} from "@app/onboarding/components/base-onboarding.component";
import {CurrencyService, PortfolioService} from "@app/services";
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import {SelectComponent} from "@app/shared/components/select/select.component";
import {SelectItem} from "@app/shared/types/select-item";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    selector: 'fingather-onboarding-step-two',
    templateUrl: 'onboarding-step-two.component.html',
    standalone: true,
    imports: [
        TranslatePipe,
        MatIcon,
        ReactiveFormsModule,
        InputValidatorComponent,
        SaveButtonComponent,
        SelectComponent,
        RouterLink
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class OnboardingStepTwoComponent extends BaseOnboardingComponent implements OnInit {
    private readonly portfolioService = inject(PortfolioService);
    private readonly currencyService = inject(CurrencyService);

    protected portfolio: Portfolio;
    protected currencies: SelectItem<number, string>[] = [];

    public override async ngOnInit(): Promise<void> {
        super.ngOnInit();

        this.$loading.set(true);

        this.portfolio = await this.portfolioService.getCurrentPortfolio();

        const currencies = await this.currencyService.getCurrencies();
        this.currencies = currencies.map((currency) => {
            return {
                key: currency.id,
                label: currency.code,
            }
        });

        this.form = this.formBuilder.group({
            currencyId: [this.portfolio.currencyId, Validators.required],
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

        this.portfolioService.notify();

        this.router.navigate(['/onboarding/step-three']);
    }
}
