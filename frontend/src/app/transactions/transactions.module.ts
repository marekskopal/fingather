import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ListComponent } from "@app/transactions/components/list/list.component";
import {LayoutComponent} from "@app/transactions/components/layout/layout.component";
import {TransactionsRoutingModule} from "@app/transactions/transactions-routing.module";
import {SharedModule} from "@app/shared/shared.module";
import {NgbPaginationModule} from "@ng-bootstrap/ng-bootstrap";
import {FormsModule, ReactiveFormsModule} from "@angular/forms";
import {TransactionDialogComponent} from "@app/transactions/components/transaction-dialog/transaction-dialog.component";


@NgModule({
    declarations: [
        LayoutComponent,
        ListComponent,
        TransactionDialogComponent
    ],
    imports: [
        CommonModule,
        TransactionsRoutingModule,
        SharedModule,
        NgbPaginationModule,
        FormsModule,
        ReactiveFormsModule,
    ]
})
export class TransactionsModule { }
