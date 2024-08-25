import {
    ChangeDetectionStrategy, Component
} from '@angular/core';
import {
    BaseTransactionListComponent
} from "@app/assets/components/detail/components/transactions/base-transaction-list.component";
import { TransactionActionType} from '@app/models';
import {TransactionList} from "@app/models/transaction-list";

@Component({
    templateUrl: 'transaction-list.component.html',
    selector: 'fingather-transaction-list',
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
