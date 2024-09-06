import {inject, Injectable, signal} from '@angular/core';
import {Router} from "@angular/router";
import {ContentLayoutService} from "@app/services/content-layout.service";
import {OnboardingStepEnum} from "@app/services/enums/onboarding-step-enum";
import {OnboardingService} from "@app/services/onboarding.service";

@Injectable({ providedIn: 'root' })
export class OnboardingProcessService {
    private readonly onboardingService = inject(OnboardingService);
    private readonly router = inject(Router);
    private readonly contentLayoutService = inject(ContentLayoutService);

    public readonly $currentStep = signal<OnboardingStepEnum>(OnboardingStepEnum.StepOne);

    public setCurrentStep(step: OnboardingStepEnum): void {
        this.$currentStep.set(step);
    }

    public async completeOnboarding(): Promise<void> {
        await this.onboardingService.onboardingComplete();

        this.contentLayoutService.setContentCenter(false);

        this.router.navigate(['/']);
    }
}
