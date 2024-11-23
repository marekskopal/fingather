import {DatePipe} from "@angular/common";
import {
    ChangeDetectionStrategy, Component,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import {DeleteButtonComponent} from "@app/shared/components/delete-button/delete-button.component";
import {PaginationComponent} from "@app/shared/components/pagination/pagination.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {TagComponent} from "@app/shared/components/tag/tag.component";
import {TickerLogoComponent} from "@app/shared/components/ticker-logo/ticker-logo.component";
import {TableGridDirective} from "@app/shared/directives/table-grid.directive";
import {SearchComponent} from "@app/transactions/components/search/search.component";
import {TransactionListComponent} from "@app/transactions/components/transaction-list/transaction-list.component";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: './transactions.component.html',
    imports: [
        PortfolioSelectorComponent,
        TranslatePipe,
        RouterLink,
        MatIcon,
        SearchComponent,
        DatePipe,
        TagComponent,
        TickerLogoComponent,
        DeleteButtonComponent,
        PaginationComponent,
        ScrollShadowDirective,
        TableGridDirective,
        TransactionListComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TransactionsComponent {
}
