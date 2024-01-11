import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ListComponent } from "@app/transactions/components/list/list.component";
import {LayoutComponent} from "@app/transactions/components/layout/layout.component";
import {TransactionsRoutingModule} from "@app/transactions/transactions-routing.module";
import {SharedModule} from "@app/shared/shared.module";
import {NgbPaginationModule} from "@ng-bootstrap/ng-bootstrap";
import {FormsModule, ReactiveFormsModule} from "@angular/forms";
import {FaIconLibrary, FontAwesomeModule} from "@fortawesome/angular-fontawesome";
import {faEdit, faPlus, faTrash} from "@fortawesome/free-solid-svg-icons";


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
        FontAwesomeModule,
    ]
})
export class TransactionsModule {
    public constructor(
        private readonly faIconLibrary: FaIconLibrary
    ) {
        faIconLibrary.addIcons(faPlus, faEdit, faTrash)
    }
}
