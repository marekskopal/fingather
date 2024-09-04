import {DatePipe} from "@angular/common";
import {
    ChangeDetectionStrategy, Component
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import {
    BaseTransactionListComponent
} from "@app/assets/components/detail/components/transactions/base-transaction-list.component";
import { TransactionActionType} from '@app/models';
import {TransactionList} from "@app/models/transaction-list";
import {DeleteButtonComponent} from "@app/shared/components/delete-button/delete-button.component";
import {TagComponent} from "@app/shared/components/tag/tag.component";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'transaction-list.component.html',
    selector: 'fingather-transaction-list',
    standalone: true,
    imports: [
        TranslateModule,
        TagComponent,
        RouterLink,
        MatIcon,
        DeleteButtonComponent,
        DatePipe
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TransactionListComponent extends BaseTransactionListComponent {
    protected async getTransactions(portfolioId: number): Promise<TransactionList> {
        return await this.transactionService.getTransactions(
            portfolioId,
            this.$asset().id,
            [
                TransactionActionType.Buy,
                TransactionActionType.Sell
            ]
        );
    }
}
