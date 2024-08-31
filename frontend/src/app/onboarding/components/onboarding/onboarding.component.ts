import {ChangeDetectionStrategy, Component, inject} from '@angular/core';
import { Router } from '@angular/router';
import { OnboardingService } from '@app/services/onboarding.service';

@Component({
    templateUrl: 'onboarding.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class OnboardingComponent {
    private readonly onboardingService = inject(OnboardingService);
    private readonly router = inject(Router);

    protected async onImportFinish(): Promise<void> {
        await this.onboardingService.onboardingComplete();

        this.router.navigate(['/']);
    }

    protected async onSkip(): Promise<void> {
        await this.onboardingService.onboardingComplete();

        this.router.navigate(['/']);
    }
}
