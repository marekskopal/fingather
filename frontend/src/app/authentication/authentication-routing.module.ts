import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { LayoutComponent } from '@app/authentication/components/layout/layout.component';
import { LoginComponent } from '@app/authentication/components/login/login.component';
import { SignUpComponent } from '@app/authentication/components/sign-up/sign-up.component';

const routes: Routes = [
    {
        path: '',
        component: LayoutComponent,
        children: [
            { path: 'login', component: LoginComponent },
            { path: 'sign-up', component: SignUpComponent }
        ]
    }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class AuthenticationRoutingModule { }
