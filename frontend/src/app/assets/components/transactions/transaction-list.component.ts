import {
    ChangeDetectionStrategy, Component, OnInit, signal
} from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Transaction, TransactionActionType } from '@app/models';
import { PortfolioService, TransactionService } from '@app/services';
import { ConfirmDialogService } from '@app/services/confirm-dialog.service';
import { TransactionDialogComponent } from '@app/shared/components/transaction-dialog/transaction-dialog.component';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
    templateUrl: 'transaction-list.component.html',
    selector: 'fingather-transaction-list',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TransactionListComponent implements OnInit {
    private $transactions = signal<Transaction[] | null>(null);
    public assetId: number;

    public constructor(
        private readonly transactionService: TransactionService,
        private readonly portfolioService: PortfolioService,
        private route: ActivatedRoute,
        private modalService: NgbModal,
        private readonly confirmDialogService: ConfirmDialogService,
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

        const transactions = await this.transactionService.getTransactions(portfolio.id, this.assetId, [
            TransactionActionType.Buy,
            TransactionActionType.Sell
        ]);

        this.$transactions.set(transactions.transactions);
    }

    protected get transactions(): Transaction[] | null {
        return this.$transactions();
    }

    public addTransaction(assetId: number): void {
        const transactionDialogComponent = this.modalService.open(TransactionDialogComponent);
        transactionDialogComponent.componentInstance.assetId = assetId;
    }

    public editTransaction(id: number): void {
        const transactionDialogComponent = this.modalService.open(TransactionDialogComponent);
        transactionDialogComponent.componentInstance.id = id;
    }

    public async deleteTransaction(id: number): Promise<void> {
        const transaction = this.$transactions()?.find((x) => x.id === id);
        if (transaction === undefined) {
            return;
        }

        await this.transactionService.deleteTransaction(id);

        this.refreshTransactions();
    }
}
