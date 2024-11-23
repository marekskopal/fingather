import {Route} from '@angular/router';
import { LayoutComponent } from '@app/authentication/components/layout/layout.component';
import { LoginComponent } from '@app/authentication/components/login/login.component';
import { SignUpComponent } from '@app/authentication/components/sign-up/sign-up.component';

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            {
                path: 'login',
                component: LoginComponent,
            },
            {
                path: 'sign-up',
                component: SignUpComponent,
            },
        ],
    },
] satisfies Route[];
