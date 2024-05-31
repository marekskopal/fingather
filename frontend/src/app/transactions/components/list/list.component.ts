import {
    ChangeDetectionStrategy, Component, OnDestroy, OnInit, signal
} from '@angular/core';
import { TransactionActionType } from '@app/models';
import { TransactionList } from '@app/models/transaction-list';
import { PortfolioService, TransactionService } from '@app/services';
import { ConfirmDialogService } from '@app/services/confirm-dialog.service';
import { DividendDialogComponent } from '@app/shared/components/dividend-dialog/dividend-dialog.component';
import { TransactionDialogComponent } from '@app/shared/components/transaction-dialog/transaction-dialog.component';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
    templateUrl: './list.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ListComponent implements OnInit, OnDestroy {
    private page: number = 1;
    public pageSize: number = 50;
    private readonly $transactionList = signal<TransactionList | null>(null);

    public constructor(
        private readonly transactionService: TransactionService,
        private readonly portfolioService: PortfolioService,
        private readonly modalService: NgbModal,
        private readonly confirmDialogService: ConfirmDialogService,
    ) {
    }

    public ngOnInit(): void {
        this.refreshTransactions();

        this.transactionService.eventEmitter.subscribe(() => {
            this.refreshTransactions();
        });

        this.portfolioService.eventEmitter.subscribe(() => {
            this.refreshTransactions();
        });
    }

    public ngOnDestroy(): void {
        this.transactionService.eventEmitter.unsubscribe();
    }

    protected async changePage(page: number): Promise<void> {
        this.page = page;

        await this.refreshTransactions();
    }

    protected async refreshTransactions(): Promise<void> {
        this.$transactionList.set(null);

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const transactionList = await this.transactionService.getTransactions(
            portfolio.id,
            null,
            null,
            this.pageSize,
            (this.page - 1) * this.pageSize
        );
        this.$transactionList.set(transactionList);
    }

    protected get transactionList(): TransactionList | null {
        return this.$transactionList();
    }

    protected addTransaction(): void {
        this.modalService.open(TransactionDialogComponent);
    }

    protected addDividend(): void {
        this.modalService.open(DividendDialogComponent);
    }

    protected editTransaction(id: number): void {
        const transactionDialogComponent = this.modalService.open(TransactionDialogComponent);
        transactionDialogComponent.componentInstance.id = id;
    }

    protected async deleteTransaction(id: number): Promise<void> {
        const transaction = this.transactionList?.transactions?.find((x) => x.id === id);
        if (transaction === undefined) {
            return;
        }
        transaction.isDeleting = true;

        try {
            const confirmed = await this.confirmDialogService.confirm(
                'Delete transaction',
                'Are you sure to delete transaction?'
            );
            if (!confirmed) {
                transaction.isDeleting = false;
                return;
            }
        } catch (err) {
            transaction.isDeleting = false;
            return;
        }

        await this.transactionService.deleteTransaction(id);

        this.refreshTransactions();
    }

    protected readonly TransactionActionType = TransactionActionType;
}
