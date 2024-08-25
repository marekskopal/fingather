import {
    ChangeDetectionStrategy, Component
} from '@angular/core';
import {
    BaseTransactionListComponent
} from "@app/assets/components/detail/components/transactions/base-transaction-list.component";
import { TransactionActionType} from '@app/models';
import {TransactionList} from "@app/models/transaction-list";

@Component({
    templateUrl: 'dividend-list.component.html',
    selector: 'fingather-dividend-list',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DividendListComponent extends BaseTransactionListComponent {
    protected async getTransactions(portfolioId: number): Promise<TransactionList> {
        return await this.transactionService.getTransactions(
            portfolioId,
            this.$asset().id,
            [
                TransactionActionType.Dividend,
                TransactionActionType.DividendTax,
            ]
        );
    }
}
