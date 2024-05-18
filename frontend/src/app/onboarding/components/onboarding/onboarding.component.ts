import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { OnboardingService } from '@app/services/onboarding.service';

@Component({ templateUrl: 'onboarding.component.html' })
export class OnboardingComponent {
    public constructor(
        private readonly onboardingService: OnboardingService,
        private readonly router: Router,
    ) {}

    protected async onImportFinish(): Promise<void> {
        await this.onboardingService.onboardingComplete();

        this.router.navigate(['/']);
    }

    protected async onSkip(): Promise<void> {
        await this.onboardingService.onboardingComplete();

        this.router.navigate(['/']);
    }
}
