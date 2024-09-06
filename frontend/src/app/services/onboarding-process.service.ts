import {inject, Injectable} from '@angular/core';
import {Router} from "@angular/router";
import {ContentLayoutService} from "@app/services/content-layout.service";
import {OnboardingService} from "@app/services/onboarding.service";

@Injectable({ providedIn: 'root' })
export class OnboardingProcessService {
    private readonly onboardingService = inject(OnboardingService);
    private readonly router = inject(Router);
    private readonly contentLayoutService = inject(ContentLayoutService);

    public async completeOnboarding(): Promise<void> {
        await this.onboardingService.onboardingComplete();

        this.contentLayoutService.setContentCenter(false);

        this.router.navigate(['/']);
    }
}
