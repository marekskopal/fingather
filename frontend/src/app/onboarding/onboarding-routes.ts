import {Route} from '@angular/router';
import {OnboardingStepOneComponent} from "@app/onboarding/components/onboarding-step-one/onboarding-step-one.component";
import {
    OnboardingStepThreeComponent,
} from "@app/onboarding/components/onboarding-step-three/onboarding-step-three.component";
import {OnboardingStepTwoComponent} from "@app/onboarding/components/onboarding-step-two/onboarding-step-two.component";
import {LayoutComponent} from "@app/shared/components/layout/layout.component";

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            {
                path: 'step-one',
                component: OnboardingStepOneComponent,
            },
            {
                path: 'step-two',
                component: OnboardingStepTwoComponent,
            },
            {
                path: 'step-three',
                component: OnboardingStepThreeComponent,
            },
        ],
    },
] satisfies Route[];
