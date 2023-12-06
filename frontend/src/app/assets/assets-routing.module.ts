import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { LayoutComponent } from './components/layout.component';
import { ListComponent } from './components/list.component';
import { AddEditComponent } from './components/add-edit.component';
import { DetailComponent } from './components/detail.component';
import { AuthGuard } from "../_helpers";

const importModule = () => import('./import/import.module').then(x => x.ImportModule);

const routes: Routes = [
    {
        path: '', component: LayoutComponent,
        children: [
            { path: '', component: ListComponent },
            { path: ':id', component: DetailComponent },
            { path: 'add', component: AddEditComponent },
            { path: 'edit/:id', component: AddEditComponent },
            { path: 'import', loadChildren: importModule, canActivate: [AuthGuard] },
        ]
    }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class AssetsRoutingModule { }
