import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { SharedModule } from '@app/shared/shared.module';
import { LayoutComponent } from '@app/transactions/components/layout/layout.component';
import { ListComponent } from '@app/transactions/components/list/list.component';
import { TransactionsRoutingModule } from '@app/transactions/transactions-routing.module';
import { NgbPaginationModule } from '@ng-bootstrap/ng-bootstrap';
import {MatIcon} from "@angular/material/icon";

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
        ReactiveFormsModule,
        MatIcon,
    ]
})
export class TransactionsModule {
}
