import {NgOptimizedImage} from "@angular/common";
import {ChangeDetectionStrategy, Component, inject, OnInit} from '@angular/core';
import {ReactiveFormsModule, Validators} from "@angular/forms";
import {MatIcon} from "@angular/material/icon";
import {Portfolio} from "@app/models";
import {PortfolioService} from "@app/services";
import {BaseForm} from "@app/shared/components/form/base-form";
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    selector: 'fingather-onboarding-step-one',
    templateUrl: 'step-one.component.html',
    standalone: true,
    imports: [
        TranslateModule,
        NgOptimizedImage,
        MatIcon,
        ReactiveFormsModule,
        InputValidatorComponent,
        SaveButtonComponent
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class StepOneComponent extends BaseForm implements OnInit {
    private readonly portfolioService = inject(PortfolioService);

    protected portfolio: Portfolio;

    public async ngOnInit(): Promise<void> {
        this.$loading.set(true);

        this.portfolio = await this.portfolioService.getCurrentPortfolio();

        this.form = this.formBuilder.group({
            name: [this.portfolio.name, Validators.required],
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

        this.alertService.success('Update successful', { keepAfterRouteChange: true });
        this.portfolioService.notify();
    }
}
