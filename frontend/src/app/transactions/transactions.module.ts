import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import { SharedModule } from '@app/shared/shared.module';
import {
    AddEditDividendFormComponent
} from "@app/transactions/components/add-edit-dividend-form/add-edit-dividend-form.component";
import {
    AddEditTransactionFormComponent
} from "@app/transactions/components/add-edit-transaction-form/add-edit-transaction-form.component";
import { LayoutComponent } from '@app/transactions/components/layout/layout.component';
import { ListComponent } from '@app/transactions/components/list/list.component';
import {SearchComponent} from "@app/transactions/components/search/search.component";
import { TransactionsRoutingModule } from '@app/transactions/transactions-routing.module';
import {
    NgbDropdown, NgbDropdownButtonItem,
    NgbDropdownItem,
    NgbDropdownMenu,
    NgbDropdownToggle,
    NgbPaginationModule
} from '@ng-bootstrap/ng-bootstrap';

@NgModule({
    declarations: [
        LayoutComponent,
        ListComponent,
        SearchComponent,
        AddEditTransactionFormComponent,
        AddEditDividendFormComponent,
    ],
    imports: [
        CommonModule,
        TransactionsRoutingModule,
        SharedModule,
        NgbPaginationModule,
        FormsModule,
        ReactiveFormsModule,
        MatIcon,
        NgbDropdown,
        NgbDropdownToggle,
        NgbDropdownMenu,
        NgbDropdownItem,
        NgbDropdownButtonItem,
    ]
})
export class TransactionsModule {
}
