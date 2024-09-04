import {Route} from '@angular/router';
import { VerifyComponent } from '@app/email-verify/components/verify/verify.component';
import {LayoutComponent} from "@app/shared/components/layout/layout.component";

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            { path: ':token', component: VerifyComponent },
        ]
    }
] satisfies Route[];

