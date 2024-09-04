import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AuthGuard } from '@app/core/guards/auth.guard';

const routes: Routes = [
    {
        path: '',
        loadChildren: () => import('./dashboard/dashboard-routes'),
        canActivate: [AuthGuard]
    },
    {
        path: 'assets',
        loadChildren: () => import('./assets/assets-routes'),
        canActivate: [AuthGuard]
    },
    {
        path: 'dividends',
        loadChildren: () => import('./dividends/dividends-routes'),
        canActivate: [AuthGuard]
    },
    {
        path: 'transactions',
        loadChildren: () => import('./transactions/transactions.module').then((x) => x.TransactionsModule),
        canActivate: [AuthGuard]
    },
    {
        path: 'overviews',
        loadChildren: () => import('./overviews/overviews-routes'),
        canActivate: [AuthGuard]
    },
    {
        path: 'history',
        loadChildren: () => import('./history/history-routes'),
        canActivate: [AuthGuard]
    },
    {
        path: 'groups',
        loadChildren: () => import('./groups/groups-routes'),
        canActivate: [AuthGuard]
    },
    {
        path: 'portfolios',
        loadChildren: () => import('./portfolios/portfolios-routes'),
        canActivate: [AuthGuard]
    },
    {
        path: 'users',
        loadChildren: () => import('./users/users.module').then((x) => x.UsersModule),
        canActivate: [AuthGuard]
    },
    {
        path: 'onboarding',
        loadChildren: () => import('./onboarding/onboarding-routes'),
        canActivate: [AuthGuard]
    },
    {
        path: 'api-keys',
        loadChildren: () => import('./api-keys/api-keys-routes'),
        canActivate: [AuthGuard]
    },
    {
        path: 'email-verify',
        loadChildren: () => import('./email-verify/email-verify-routes'),
    },
    {
        path: 'authentication',
        loadChildren: () => import('./authentication/authentication-routes'),
    },

    // otherwise redirect to home
    { path: '**', redirectTo: '' }
];

@NgModule({
    imports: [RouterModule.forRoot(routes)],
    exports: [RouterModule]
})
export class AppRoutingModule { }
