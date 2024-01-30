import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {SharedModule} from '@app/shared/shared.module';
import {LayoutComponent} from '@app/transactions/components/layout/layout.component';
import { ListComponent } from '@app/transactions/components/list/list.component';
import {TransactionsRoutingModule} from '@app/transactions/transactions-routing.module';
import {FaIconLibrary, FontAwesomeModule} from '@fortawesome/angular-fontawesome';
import {faEdit, faPlus, faTrash} from '@fortawesome/free-solid-svg-icons';
import {NgbPaginationModule} from '@ng-bootstrap/ng-bootstrap';
import {MomentModule} from 'ngx-moment';


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
        MomentModule,
    ]
})
export class TransactionsModule {
    public constructor(
        private readonly faIconLibrary: FaIconLibrary
    ) {
        faIconLibrary.addIcons(faPlus, faEdit, faTrash)
    }
}
