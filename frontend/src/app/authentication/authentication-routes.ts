import {Route} from '@angular/router';
import { GoogleSignUpComponent } from '@app/authentication/components/google-sign-up/google-sign-up.component';
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
            {
                path: 'google-sign-up',
                component: GoogleSignUpComponent,
            },
        ],
    },
] satisfies Route[];
