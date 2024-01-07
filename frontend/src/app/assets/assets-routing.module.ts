import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { LayoutComponent } from './components/layout.component';
import { ListComponent } from './components/list.component';
import { DetailComponent } from './components/detail.component';
import {AuthGuard} from "@app/core/guards/auth.guard";

const routes: Routes = [
    {
        path: '', component: LayoutComponent,
        children: [
            { path: '', component: ListComponent },
            { path: 'import', loadChildren: () => import('./import/import.module').then(x => x.ImportModule), canActivate: [AuthGuard] },
            { path: ':id', component: DetailComponent },
        ]
    }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class AssetsRoutingModule { }
