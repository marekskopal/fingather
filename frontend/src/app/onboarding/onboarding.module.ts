import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { LayoutComponent } from '@app/onboarding/components/layout/layout.component';
import { OnboardingComponent } from '@app/onboarding/components/onboarding/onboarding.component';
import { PortfolioFormComponent } from '@app/onboarding/components/portfolio-form/portfolio-form.component';
import {ImportComponent} from "@app/shared/components/import/import.component";
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {SelectComponent} from "@app/shared/components/select/select.component";
import {TranslateModule} from "@ngx-translate/core";

import { OnboardingRoutingModule } from './onboarding-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        OnboardingRoutingModule,
        InputValidatorComponent,
        TranslateModule,
        SelectComponent,
        ImportComponent,
    ],
    declarations: [
        LayoutComponent,
        OnboardingComponent,
        PortfolioFormComponent,
    ]
})
export class OnboardingModule {
}
