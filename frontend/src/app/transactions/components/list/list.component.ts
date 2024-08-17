import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit, signal
} from '@angular/core';
import { TransactionActionType } from '@app/models';
import { TransactionList } from '@app/models/transaction-list';
import { PortfolioService, TransactionService } from '@app/services';
import { DividendDialogComponent } from '@app/shared/components/dividend-dialog/dividend-dialog.component';
import { TransactionDialogComponent } from '@app/shared/components/transaction-dialog/transaction-dialog.component';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import {TransactionSearch} from "@app/transactions/types/transaction-search";

@Component({
    templateUrl: './list.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ListComponent implements OnInit {
    private page: number = 1;
    public pageSize: number = 50;

    private readonly $transactionList = signal<TransactionList | null>(null);

    private readonly $transactionSearch = signal<TransactionSearch>({
        search: null,
        selectedType: null,
        created: null,
    });

    public constructor(
        private readonly transactionService: TransactionService,
        private readonly portfolioService: PortfolioService,
        private readonly modalService: NgbModal,
        private readonly changeDetectorRef: ChangeDetectorRef,
    ) {
    }

    public ngOnInit(): void {
        this.refreshTransactions();

        this.transactionService.subscribe(() => {
            this.refreshTransactions();
            this.changeDetectorRef.detectChanges();
        });

        this.portfolioService.subscribe(() => {
            this.refreshTransactions();
            this.changeDetectorRef.detectChanges();
        });
    }

    protected async changePage(page: number): Promise<void> {
        this.page = page;

        await this.refreshTransactions();
    }

    protected async refreshTransactions(): Promise<void> {
        this.$transactionList.set(null);

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const transactionSearch = this.$transactionSearch();

        const transactionList = await this.transactionService.getTransactions(
            portfolio.id,
            null,
            transactionSearch.selectedType !== null ? [transactionSearch.selectedType] : null,
            transactionSearch.search,
            transactionSearch.created,
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

        await this.transactionService.deleteTransaction(id);

        this.refreshTransactions();
    }

    protected async changePageSize(pageSize: number): Promise<void> {
        this.pageSize = pageSize;
        await this.refreshTransactions();
    }

    protected changeTransactionSearch(transactionSearch: TransactionSearch): void {
        this.$transactionSearch.set(transactionSearch);

        this.refreshTransactions();
    }
}
