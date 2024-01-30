import { Component, OnDestroy, OnInit } from '@angular/core';
import { TransactionList } from '@app/models/TransactionList';
import { PortfolioService, TransactionService } from '@app/services';
import { ConfirmDialogService } from '@app/services/confirm-dialog.service';
import { DividendDialogComponent } from '@app/shared/components/dividend-dialog/dividend-dialog.component';
import { TransactionDialogComponent } from '@app/shared/components/transaction-dialog/transaction-dialog.component';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { first } from 'rxjs/operators';

@Component({
    templateUrl: './list.component.html',
})
export class ListComponent implements OnInit, OnDestroy {
    public page: number = 1;
    public pageSize: number = 50;
    public transactionList: TransactionList | null = null;

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

    public async refreshTransactions(): Promise<void> {
        this.transactionList = null;

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        this.transactionService.getTransactions(
            portfolio.id,
            null,
            null,
            this.pageSize,
            (this.page - 1) * this.pageSize
        )
            .pipe(first())
            .subscribe((transactionList) => this.transactionList = transactionList);
    }

    public addTransaction(): void {
        this.modalService.open(TransactionDialogComponent);
    }

    public addDividend(): void {
        this.modalService.open(DividendDialogComponent);
    }

    public editTransaction(id: number): void {
        const transactionDialogComponent = this.modalService.open(TransactionDialogComponent);
        transactionDialogComponent.componentInstance.id = id;
    }

    public async deleteTransaction(id: number): Promise<void> {
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

        this.transactionService.deleteTransaction(id)
            .pipe(first())
            .subscribe(() => this.refreshTransactions());
    }
}
