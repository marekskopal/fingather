import {ChangeDetectionStrategy, Component, inject} from '@angular/core';
import { Router } from '@angular/router';
import {PortfolioFormComponent} from "@app/onboarding/components/portfolio-form/portfolio-form.component";
import { OnboardingService } from '@app/services/onboarding.service';
import {ImportComponent} from "@app/shared/components/import/import.component";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'onboarding.component.html',
    standalone: true,
    imports: [
        TranslateModule,
        PortfolioFormComponent,
        ImportComponent
    ],
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
