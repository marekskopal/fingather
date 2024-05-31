import {
    ChangeDetectionStrategy, Component, OnDestroy, OnInit, signal
} from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Transaction, TransactionActionType } from '@app/models';
import { PortfolioService, TransactionService } from '@app/services';
import { ConfirmDialogService } from '@app/services/confirm-dialog.service';
import { DividendDialogComponent } from '@app/shared/components/dividend-dialog/dividend-dialog.component';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
    templateUrl: 'dividend-list.component.html',
    selector: 'fingather-dividend-list',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DividendListComponent implements OnInit, OnDestroy {
    private $dividends = signal<Transaction[] | null>(null);
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

    public ngOnDestroy(): void {
        this.transactionService.unsubscribe();
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

    protected addDividend(): void {
        const dividendDialogComponent = this.modalService.open(DividendDialogComponent);
        dividendDialogComponent.componentInstance.assetId = this.assetId;
    }

    protected editDividend(id: number): void {
        const dividendDialogComponent = this.modalService.open(DividendDialogComponent);
        dividendDialogComponent.componentInstance.id = id;
    }

    protected async deleteDividend(id: number): Promise<void> {
        const transaction = this.$dividends()?.find((x) => x.id === id);
        if (transaction === undefined) {
            return;
        }
        transaction.isDeleting = true;

        try {
            const confirmed = await this.confirmDialogService.confirm(
                'Delete dividend',
                'Are you sure to delete dividend?'
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
}
