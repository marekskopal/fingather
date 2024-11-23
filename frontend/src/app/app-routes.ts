import { Routes} from '@angular/router';
import { AuthGuard } from '@app/core/guards/auth.guard';

export const appRoutes: Routes = [
    {
        path: '',
        loadChildren: () => import('./dashboard/dashboard-routes'),
        canActivate: [AuthGuard],
    },
    {
        path: 'assets',
        loadChildren: () => import('./assets/assets-routes'),
        canActivate: [AuthGuard],
    },
    {
        path: 'dividends',
        loadChildren: () => import('./dividends/dividends-routes'),
        canActivate: [AuthGuard],
    },
    {
        path: 'transactions',
        loadChildren: () => import('./transactions/transactions-routes'),
        canActivate: [AuthGuard],
    },
    {
        path: 'overviews',
        loadChildren: () => import('./overviews/overviews-routes'),
        canActivate: [AuthGuard],
    },
    {
        path: 'history',
        loadChildren: () => import('./history/history-routes'),
        canActivate: [AuthGuard],
    },
    {
        path: 'groups',
        loadChildren: () => import('./groups/groups-routes'),
        canActivate: [AuthGuard],
    },
    {
        path: 'portfolios',
        loadChildren: () => import('./portfolios/portfolios-routes'),
        canActivate: [AuthGuard],
    },
    {
        path: 'users',
        loadChildren: () => import('./users/users-routes'),
        canActivate: [AuthGuard],
    },
    {
        path: 'onboarding',
        loadChildren: () => import('./onboarding/onboarding-routes'),
        canActivate: [AuthGuard],
    },
    {
        path: 'api-keys',
        loadChildren: () => import('./api-keys/api-keys-routes'),
        canActivate: [AuthGuard],
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
    { path: '**', redirectTo: '' },
];
