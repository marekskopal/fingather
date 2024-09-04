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
        loadChildren: () => import('./dividends/dividends.module').then((x) => x.DividendsModule),
        canActivate: [AuthGuard]
    },
    {
        path: 'transactions',
        loadChildren: () => import('./transactions/transactions.module').then((x) => x.TransactionsModule),
        canActivate: [AuthGuard]
    },
    {
        path: 'overviews',
        loadChildren: () => import('./overviews/overviews.module').then((x) => x.OverviewsModule),
        canActivate: [AuthGuard]
    },
    {
        path: 'history',
        loadChildren: () => import('./history/history.module').then((x) => x.HistoryModule),
        canActivate: [AuthGuard]
    },
    {
        path: 'groups',
        loadChildren: () => import('./groups/groups.module').then((x) => x.GroupsModule),
        canActivate: [AuthGuard]
    },
    {
        path: 'portfolios',
        loadChildren: () => import('./portfolios/portfolios.module').then((x) => x.PortfoliosModule),
        canActivate: [AuthGuard]
    },
    {
        path: 'users',
        loadChildren: () => import('./users/users.module').then((x) => x.UsersModule),
        canActivate: [AuthGuard]
    },
    {
        path: 'onboarding',
        loadChildren: () => import('./onboarding/onboarding.module').then((x) => x.OnboardingModule),
        canActivate: [AuthGuard]
    },
    {
        path: 'api-keys',
        loadChildren: () => import('./api-keys/api-keys-routes'),
        canActivate: [AuthGuard]
    },
    {
        path: 'email-verify',
        loadChildren: () => import('./email-verify/email-verify.module').then((x) => x.EmailVerifyModule)
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
