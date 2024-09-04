import {Route} from '@angular/router';
import { OnboardingComponent } from '@app/onboarding/components/onboarding/onboarding.component';
import {LayoutComponent} from "@app/shared/components/layout/layout.component";

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            {
                path: '',
                component: OnboardingComponent
            },
        ]
    }
] satisfies Route[];
