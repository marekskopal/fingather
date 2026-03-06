import {Route} from '@angular/router';
import { ForgotPasswordComponent } from '@app/authentication/components/forgot-password/forgot-password.component';
import { GoogleSignUpComponent } from '@app/authentication/components/google-sign-up/google-sign-up.component';
import { LayoutComponent } from '@app/authentication/components/layout/layout.component';
import { LoginComponent } from '@app/authentication/components/login/login.component';
import { ResetPasswordComponent } from '@app/authentication/components/reset-password/reset-password.component';
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
            {
                path: 'forgot-password',
                component: ForgotPasswordComponent,
            },
            {
                path: 'reset-password/:token',
                component: ResetPasswordComponent,
            },
        ],
    },
] satisfies Route[];
