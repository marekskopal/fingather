import {ChangeDetectionStrategy, Component} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import {BaseOnboardingComponent} from "@app/onboarding/components/base-onboarding.component";
import {ImportComponent} from "@app/shared/components/import/import.component";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    selector: 'fingather-onboarding-step-three',
    templateUrl: 'onboarding-step-three.component.html',
    standalone: true,
    imports: [
        TranslateModule,
        MatIcon,
        RouterLink,
        ImportComponent
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class OnboardingStepThreeComponent extends BaseOnboardingComponent {
    public async onSubmit(): Promise<void> {
    }

    protected async onImportFinish(): Promise<void> {
        this.onboardingProcessService.completeOnboarding();
    }
}
