import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AuthGuard } from '@app/core/guards/auth.guard';

import { DetailComponent } from './components/detail.component';
import { LayoutComponent } from './components/layout.component';
import { ListComponent } from './components/list.component';

const routes: Routes = [
    {
        path: '',
        component: LayoutComponent,
        children: [
            { path: '', component: ListComponent },
            {
                path: 'import',
                loadChildren: () => import('./import/import.module').then((x) => x.ImportModule),
                canActivate: [AuthGuard]
            },
            { path: ':id', component: DetailComponent },
        ]
    }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class AssetsRoutingModule { }
