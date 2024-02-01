import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DetailComponent } from '@app/assets/components/detail/detail.component';
import { LayoutComponent } from '@app/assets/components/layout/layout.component';
import { ListComponent } from '@app/assets/components/list/list.component';
import { AuthGuard } from '@app/core/guards/auth.guard';

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
