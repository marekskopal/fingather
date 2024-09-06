import {ChangeDetectionStrategy, Component, inject, OnInit} from '@angular/core';
import {StepOneComponent} from "@app/onboarding/components/onboarding/components/step-one/step-one.component";
import {PortfolioFormComponent} from "@app/onboarding/components/portfolio-form/portfolio-form.component";
import {ContentLayoutService} from "@app/services/content-layout.service";
import {OnboardingStepEnum} from "@app/services/enums/onboarding-step-enum";
import {OnboardingProcessService} from "@app/services/onboarding-process.service";
import {ImportComponent} from "@app/shared/components/import/import.component";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'onboarding.component.html',
    standalone: true,
    imports: [
        TranslateModule,
        PortfolioFormComponent,
        ImportComponent,
        StepOneComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class OnboardingComponent implements OnInit {
    private readonly onboardingProcessService = inject(OnboardingProcessService);
    private readonly contentLayoutService = inject(ContentLayoutService);

    protected readonly $currentStep = this.onboardingProcessService.$currentStep;

    public ngOnInit(): void {
        this.contentLayoutService.setContentCenter(true);
    }

    protected async onSkip(): Promise<void> {
        await this.onboardingProcessService.completeOnboarding();
    }

    protected readonly OnboardingStepEnum = OnboardingStepEnum;
}
