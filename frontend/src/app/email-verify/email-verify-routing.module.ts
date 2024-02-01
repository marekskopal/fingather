import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { LayoutComponent } from '@app/email-verify/components/layout/layout.component';
import { VerifyComponent } from '@app/email-verify/components/verify/verify.component';

const routes: Routes = [
    {
        path: '',
        component: LayoutComponent,
        children: [
            { path: ':token', component: VerifyComponent },
        ]
    }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class EmailVerifyRoutingModule { }
