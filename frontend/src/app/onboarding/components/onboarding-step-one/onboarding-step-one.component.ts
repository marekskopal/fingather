import {NgOptimizedImage} from "@angular/common";
import {ChangeDetectionStrategy, Component, inject, OnInit} from '@angular/core';
import {ReactiveFormsModule, Validators} from "@angular/forms";
import {MatIcon} from "@angular/material/icon";
import {Portfolio} from "@app/models";
import {BaseOnboardingComponent} from "@app/onboarding/components/base-onboarding.component";
import {PortfolioService} from "@app/services";
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    selector: 'fingather-onboarding-step-one',
    templateUrl: 'onboarding-step-one.component.html',
    imports: [
        TranslatePipe,
        NgOptimizedImage,
        MatIcon,
        ReactiveFormsModule,
        InputValidatorComponent,
        SaveButtonComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class OnboardingStepOneComponent extends BaseOnboardingComponent implements OnInit {
    private readonly portfolioService = inject(PortfolioService);

    protected portfolio: Portfolio;

    public override async ngOnInit(): Promise<void> {
        super.ngOnInit();

        this.loading.set(true);

        this.portfolio = await this.portfolioService.getCurrentPortfolio();

        this.form = this.formBuilder.group({
            name: [this.portfolio.name, Validators.required],
        });

        this.loading.set(false);
    }

    public async onSubmit(): Promise<void> {
        this.submitted.set(true);

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.saving.set(true);
        try {
            this.updatePortfolio(this.portfolio.id);
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.saving.set(false);
        }
    }

    private async updatePortfolio(portfolioId: number): Promise<void> {
        await this.portfolioService.updatePortfolio(portfolioId, this.form.value);

        this.portfolioService.notify();

        this.router.navigate(['/onboarding/step-two']);
    }
}
