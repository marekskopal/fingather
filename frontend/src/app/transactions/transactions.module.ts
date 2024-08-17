import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { SharedModule } from '@app/shared/shared.module';
import { LayoutComponent } from '@app/transactions/components/layout/layout.component';
import { ListComponent } from '@app/transactions/components/list/list.component';
import { TransactionsRoutingModule } from '@app/transactions/transactions-routing.module';
import {
    NgbDropdown, NgbDropdownButtonItem,
    NgbDropdownItem,
    NgbDropdownMenu,
    NgbDropdownToggle,
    NgbPaginationModule
} from '@ng-bootstrap/ng-bootstrap';
import {MatIcon} from "@angular/material/icon";
import {SearchComponent} from "@app/transactions/components/search/search.component";

@NgModule({
    declarations: [
        LayoutComponent,
        ListComponent,
        SearchComponent,
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
