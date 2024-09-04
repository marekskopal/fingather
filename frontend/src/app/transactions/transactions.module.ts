import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import {DateInputComponent} from "@app/shared/components/date-input/date-input.component";
import {DeleteButtonComponent} from "@app/shared/components/delete-button/delete-button.component";
import {ImportComponent} from "@app/shared/components/import/import.component";
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {PaginationComponent} from "@app/shared/components/pagination/pagination.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import {SearchInputComponent} from "@app/shared/components/search-input/search-input.component";
import {SelectComponent} from "@app/shared/components/select/select.component";
import {TagComponent} from "@app/shared/components/tag/tag.component";
import {TickerLogoComponent} from "@app/shared/components/ticker-logo/ticker-logo.component";
import {TypeSelectComponent} from "@app/shared/components/type-select/type-select.component";
import {
    AddEditDividendFormComponent
} from "@app/transactions/components/add-edit-dividend-form/add-edit-dividend-form.component";
import {
    AddEditTransactionFormComponent
} from "@app/transactions/components/add-edit-transaction-form/add-edit-transaction-form.component";
import {ImportTransactionsComponent} from "@app/transactions/components/import/import-transactions.component";
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
import {TranslateModule} from "@ngx-translate/core";

@NgModule({
    declarations: [
        LayoutComponent,
        ListComponent,
        SearchComponent,
        AddEditTransactionFormComponent,
        AddEditDividendFormComponent,
        ImportTransactionsComponent,
    ],
    imports: [
        CommonModule,
        TransactionsRoutingModule,
        NgbPaginationModule,
        FormsModule,
        ReactiveFormsModule,
        MatIcon,
        NgbDropdown,
        NgbDropdownToggle,
        NgbDropdownMenu,
        NgbDropdownItem,
        NgbDropdownButtonItem,
        InputValidatorComponent,
        SaveButtonComponent,
        SearchInputComponent,
        TagComponent,
        DateInputComponent,
        TranslateModule,
        PortfolioSelectorComponent,
        DeleteButtonComponent,
        PaginationComponent,
        TickerLogoComponent,
        ImportComponent,
        SelectComponent,
        TypeSelectComponent,
    ]
})
export class TransactionsModule {
}
