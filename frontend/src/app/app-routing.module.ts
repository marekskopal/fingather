import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import {AuthGuard} from "@app/core/guards/auth.guard";


const portfolioModule = () => import('./portfolio/portfolio.module').then(x => x.PortfolioModule);
const authenticationModule = () => import('./authentication/authentication.module').then(x => x.AuthenticationModule);
const assetsModule = () => import('./assets/assets.module').then(x => x.AssetsModule);
const groupsModule = () => import('./groups/groups.module').then(x => x.GroupsModule);
const brokersModule = () => import('./brokers/brokers.module').then(x => x.BrokersModule);
const usersModule = () => import('./users/users.module').then(x => x.UsersModule);

const routes: Routes = [
    { path: '', loadChildren: portfolioModule, canActivate: [AuthGuard] },
    { path: 'assets', loadChildren: assetsModule, canActivate: [AuthGuard] },
    { path: 'groups', loadChildren: groupsModule, canActivate: [AuthGuard] },
    { path: 'brokers', loadChildren: brokersModule, canActivate: [AuthGuard] },
    { path: 'users', loadChildren: usersModule, canActivate: [AuthGuard] },
    { path: 'authentication', loadChildren: authenticationModule },

    // otherwise redirect to home
    { path: '**', redirectTo: '' }
];

@NgModule({
    imports: [RouterModule.forRoot(routes)],
    exports: [RouterModule]
})
export class AppRoutingModule { }
