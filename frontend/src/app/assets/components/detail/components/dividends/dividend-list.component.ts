import {
    ChangeDetectionStrategy, Component, OnInit, signal
} from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Transaction, TransactionActionType } from '@app/models';
import { PortfolioService, TransactionService } from '@app/services';

@Component({
    templateUrl: 'dividend-list.component.html',
    selector: 'fingather-dividend-list',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DividendListComponent implements OnInit {
    private $dividends = signal<Transaction[] | null>(null);
    public assetId: number;

    public constructor(
        private readonly transactionService: TransactionService,
        private readonly portfolioService: PortfolioService,
        private route: ActivatedRoute,
    ) {}

    public ngOnInit(): void {
        this.assetId = this.route.snapshot.params['id'];

        this.refreshTransactions();

        this.transactionService.subscribe(() => {
            this.refreshTransactions();
        });
    }

    public async refreshTransactions(): Promise<void> {
        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const transactions = await this.transactionService.getTransactions(
            portfolio.id,
            this.assetId,
            [TransactionActionType.Dividend]
        );

        this.$dividends.set(transactions.transactions);
    }

    protected get dividends(): Transaction[] | null {
        return this.$dividends();
    }

    protected async deleteDividend(id: number): Promise<void> {
        const transaction = this.$dividends()?.find((x) => x.id === id);
        if (transaction === undefined) {
            return;
        }

        await this.transactionService.deleteTransaction(id);

        this.refreshTransactions();
    }
}
