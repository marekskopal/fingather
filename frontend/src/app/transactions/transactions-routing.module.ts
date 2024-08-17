import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AuthGuard } from '@app/core/guards/auth.guard';
import { LayoutComponent } from '@app/transactions/components/layout/layout.component';
import { ListComponent } from '@app/transactions/components/list/list.component';
import {
    AddEditTransactionFormComponent
} from "@app/transactions/components/add-edit-transaction-form/add-edit-transaction-form.component";
import {
    AddEditDividendFormComponent
} from "@app/transactions/components/add-edit-dividend-form/add-edit-dividend-form.component";

const routes: Routes = [
    {
        path: '',
        component: LayoutComponent,
        children: [
            { path: '', component: ListComponent },
            { path: 'add-transaction', component: AddEditTransactionFormComponent },
            { path: 'edit-transaction/:id', component: AddEditTransactionFormComponent },
            { path: 'add-dividend', component: AddEditDividendFormComponent },
            { path: 'edit-dividend/:id', component: AddEditDividendFormComponent },
            {
                path: 'import',
                loadChildren: () => import('./import/import.module').then((x) => x.ImportModule),
                canActivate: [AuthGuard]
            },
        ]
    }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class TransactionsRoutingModule { }
