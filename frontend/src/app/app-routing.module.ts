import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import {AuthGuard} from "@app/core/guards/auth.guard";

const routes: Routes = [
    { path: '', loadChildren: () => import('./portfolio/portfolio.module').then(x => x.PortfolioModule), canActivate: [AuthGuard] },
    { path: 'assets', loadChildren: () => import('./assets/assets.module').then(x => x.AssetsModule), canActivate: [AuthGuard] },
    { path: 'overviews', loadChildren: () => import('./overviews/overviews.module').then(x => x.OverviewsModule), canActivate: [AuthGuard] },
    { path: 'groups', loadChildren: () => import('./groups/groups.module').then(x => x.GroupsModule), canActivate: [AuthGuard] },
    { path: 'brokers', loadChildren: () => import('./brokers/brokers.module').then(x => x.BrokersModule), canActivate: [AuthGuard] },
    { path: 'users', loadChildren: () => import('./users/users.module').then(x => x.UsersModule), canActivate: [AuthGuard] },
    { path: 'authentication', loadChildren: () => import('./authentication/authentication.module').then(x => x.AuthenticationModule) },

    // otherwise redirect to home
    { path: '**', redirectTo: '' }
];

@NgModule({
    imports: [RouterModule.forRoot(routes)],
    exports: [RouterModule]
})
export class AppRoutingModule { }
