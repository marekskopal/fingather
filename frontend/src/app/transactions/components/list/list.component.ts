import {Component, OnDestroy, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {PortfolioService, TransactionService} from "@app/services";
import {TransactionList} from "@app/models/TransactionList";
import {NgbModal} from "@ng-bootstrap/ng-bootstrap";
import {TransactionDialogComponent} from "@app/shared/components/transaction-dialog/transaction-dialog.component";
import {DividendDialogComponent} from "@app/shared/components/dividend-dialog/dividend-dialog.component";

@Component({
    templateUrl: './list.component.html',
})
export class ListComponent implements OnInit, OnDestroy {
    public page: number = 1;
    public pageSize: number = 50;
    public transactionList: TransactionList|null = null;

    public constructor(
        private readonly transactionService: TransactionService,
        private readonly portfolioService: PortfolioService,
        private readonly modalService: NgbModal,
    ) {
    }

    public ngOnInit(): void {
        this.refreshTransactions();

        this.transactionService.eventEmitter.subscribe(() => {
            this.refreshTransactions();
        });
    }

    public ngOnDestroy(): void {
        this.transactionService.eventEmitter.unsubscribe();
    }

    public async refreshTransactions(): Promise<void> {
        const portfolio = await this.portfolioService.getDefaultPortfolio();

        this.transactionService.getTransactions(portfolio.id, null, null, this.pageSize, (this.page - 1) * this.pageSize)
            .pipe(first())
            .subscribe(transactionList => this.transactionList = transactionList);
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

    public deleteTransaction(id: number): void {
        const transaction = this.transactionList?.transactions?.find(x => x.id === id);
        if (transaction === undefined) {
            return;
        }
        transaction.isDeleting = true;
        this.transactionService.deleteTransaction(id)
            .pipe(first())
            .subscribe(() => this.refreshTransactions());
    }
}
