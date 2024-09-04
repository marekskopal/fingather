import {Route} from '@angular/router';
import {LayoutComponent} from "@app/shared/components/layout/layout.component";
import {
    AddEditDividendFormComponent
} from "@app/transactions/components/add-edit-dividend-form/add-edit-dividend-form.component";
import {
    AddEditTransactionFormComponent
} from "@app/transactions/components/add-edit-transaction-form/add-edit-transaction-form.component";
import {ImportTransactionsComponent} from "@app/transactions/components/import/import-transactions.component";
import { ListComponent } from '@app/transactions/components/list/list.component';

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            {
                path: '',
                component: ListComponent
            },
            {
                path: 'add-transaction',
                component: AddEditTransactionFormComponent
            },
            {
                path: 'edit-transaction/:id',
                component: AddEditTransactionFormComponent
            },
            {
                path: 'add-dividend',
                component: AddEditDividendFormComponent
            },
            {
                path: 'edit-dividend/:id',
                component: AddEditDividendFormComponent
            },
            {
                path: 'import',
                component: ImportTransactionsComponent
            },
        ]
    }
] satisfies Route[];
