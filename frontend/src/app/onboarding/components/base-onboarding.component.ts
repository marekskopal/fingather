import {ChangeDetectionStrategy, Component, inject, OnInit} from '@angular/core';
import { Router} from "@angular/router";
import {ContentLayoutService} from "@app/services/content-layout.service";
import {OnboardingProcessService} from "@app/services/onboarding-process.service";
import {BaseForm} from "@app/shared/components/form/base-form";

@Component({
    template: '',
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export abstract class BaseOnboardingComponent extends BaseForm implements OnInit {
    protected readonly onboardingProcessService = inject(OnboardingProcessService);
    protected readonly router = inject(Router);
    private readonly contentLayoutService = inject(ContentLayoutService);

    public ngOnInit(): void {
        this.contentLayoutService.setContentCenter(true);
    }

    protected async onSkip(): Promise<void> {
        await this.onboardingProcessService.completeOnboarding();
    }
}
