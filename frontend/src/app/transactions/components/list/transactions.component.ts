import {
    ChangeDetectionStrategy, Component,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {TransactionListComponent} from "@app/transactions/components/transaction-list/transaction-list.component";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: './transactions.component.html',
    imports: [
        PortfolioSelectorComponent,
        TranslatePipe,
        RouterLink,
        MatIcon,
        TransactionListComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TransactionsComponent {
}
