import {
    ChangeDetectionStrategy, Component, inject, input, OnInit, signal
} from '@angular/core';
import {AssetWithProperties, Transaction} from '@app/models';
import {TransactionList} from "@app/models/transaction-list";
import { PortfolioService, TransactionService } from '@app/services';

@Component({
    template: '',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export abstract class BaseTransactionListComponent implements OnInit {
    protected readonly transactionService = inject(TransactionService);
    private readonly portfolioService = inject(PortfolioService);

    public readonly $asset = input.required<AssetWithProperties>({
        alias: 'asset',
    });

    private $transactions = signal<Transaction[] | null>(null);

    public ngOnInit(): void {
        this.refreshTransactions();

        this.transactionService.subscribe(() => {
            this.refreshTransactions();
        });
    }

    public async refreshTransactions(): Promise<void> {
        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const transactions = await this.getTransactions(portfolio.id);

        this.$transactions.set(transactions.transactions);
    }

    protected abstract getTransactions(portfolioId: number): Promise<TransactionList>;

    protected get transactions(): Transaction[] | null {
        return this.$transactions();
    }

    protected async deleteTransaction(id: number): Promise<void> {
        const transaction = this.$transactions()?.find((x) => x.id === id);
        if (transaction === undefined) {
            return;
        }

        await this.transactionService.deleteTransaction(id);

        this.refreshTransactions();
    }
}
