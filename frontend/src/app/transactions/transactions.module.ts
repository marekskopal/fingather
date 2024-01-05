import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ListComponent } from "@app/transactions/components/list/list.component";
import {LayoutComponent} from "@app/transactions/components/layout/layout.component";
import {TransactionsRoutingModule} from "@app/transactions/transactions-routing.module";
import {SharedModule} from "@app/shared/shared.module";
import {NgbPaginationModule} from "@ng-bootstrap/ng-bootstrap";
import {FormsModule} from "@angular/forms";


@NgModule({
    declarations: [
        LayoutComponent,
        ListComponent,
    ],
    imports: [
        CommonModule,
        TransactionsRoutingModule,
        SharedModule,
        NgbPaginationModule,
        FormsModule,
    ]
})
export class TransactionsModule { }
