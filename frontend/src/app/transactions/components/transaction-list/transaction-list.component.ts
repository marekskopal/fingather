import {DatePipe} from "@angular/common";
import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, computed, inject, input, OnInit, signal,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import {TransactionActionType} from "@app/models";
import { TransactionList } from '@app/models/transaction-list';
import { PortfolioService, TransactionService } from '@app/services';
import {DeleteButtonComponent} from "@app/shared/components/delete-button/delete-button.component";
import {PaginationComponent} from "@app/shared/components/pagination/pagination.component";
import {TagComponent} from "@app/shared/components/tag/tag.component";
import {TickerLogoComponent} from "@app/shared/components/ticker-logo/ticker-logo.component";
import {TableGridDirective} from "@app/shared/directives/table-grid.directive";
import {TableGridColumn} from "@app/shared/types/table-grid-column";
import {SearchComponent} from "@app/transactions/components/search/search.component";
import {
    TransactionGridColumnEnum,
} from "@app/transactions/components/transaction-list/enums/transaction-grid-column-enum";
import {TransactionSearch} from "@app/transactions/types/transaction-search";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    selector: 'fingather-transaction-list',
    templateUrl: './transaction-list.component.html',
    imports: [
        TranslatePipe,
        RouterLink,
        MatIcon,
        SearchComponent,
        DatePipe,
        TagComponent,
        TickerLogoComponent,
        DeleteButtonComponent,
        PaginationComponent,
        ScrollShadowDirective,
        TableGridDirective,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TransactionListComponent implements OnInit {
    private readonly transactionService = inject(TransactionService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly changeDetectorRef = inject(ChangeDetectorRef);

    public readonly assetId = input<number | null>(null);

    public readonly actionTypes = input<TransactionActionType[] | null>(null);

    public readonly columns = input<TransactionGridColumnEnum[]>([
        TransactionGridColumnEnum.Date,
        TransactionGridColumnEnum.Created,
        TransactionGridColumnEnum.Type,
        TransactionGridColumnEnum.Asset,
        TransactionGridColumnEnum.Actions,
    ]);

    public readonly showSearch = input<boolean>(true);

    public readonly showCard = input<boolean>(true);

    public readonly showPagination = input<boolean>(true);

    private page: number = 1;
    public pageSize: number = 50;

    protected readonly transactionList = signal<TransactionList | null>(null);

    private readonly transactionSearch = signal<TransactionSearch>({
        search: null,
        selectedType: null,
        created: null,
    });

    protected readonly tableGridColumns = computed<TableGridColumn[]>(() => {
        const columns: TableGridColumn[] = [];

        for (const column of this.columns()) {
            switch (column) {
                case TransactionGridColumnEnum.Date:
                    columns.push({ min: '130px', max: '1.2fr' });
                    break;
                case TransactionGridColumnEnum.Created:
                    columns.push({ min: '130px', max: '1.2fr' });
                    break;
                case TransactionGridColumnEnum.Type:
                    columns.push({ min: '130px', max: '1.2fr' });
                    break;
                case TransactionGridColumnEnum.Asset:
                    columns.push({ min: '250px', max: '3fr' });
                    break;
                case TransactionGridColumnEnum.Actions:
                    columns.push({ min: '124px', max: '1fr' });
                    break;
            }
        }

        return columns;
    });

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
        this.transactionList.set(null);

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const transactionSearch = this.transactionSearch();

        const transactionList = await this.transactionService.getTransactions(
            portfolio.id,
            this.assetId(),
            transactionSearch.selectedType !== null ? [transactionSearch.selectedType] : this.actionTypes(),
            transactionSearch.search,
            transactionSearch.created,
            this.showPagination() ? this.pageSize : null,
            this.showPagination() ? (this.page - 1) * this.pageSize : null,
        );
        this.transactionList.set(transactionList);
    }

    protected async deleteTransaction(id: number): Promise<void> {
        const transaction = this.transactionList()?.transactions?.find((x) => x.id === id);
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
        this.transactionSearch.set(transactionSearch);

        this.refreshTransactions();
    }

    protected async exportCsv(): Promise<void> {
        const portfolio = await this.portfolioService.getCurrentPortfolio();
        const transactionSearch = this.transactionSearch();
        await this.transactionService.exportCsv(
            portfolio.id,
            this.assetId(),
            transactionSearch.selectedType !== null ? [transactionSearch.selectedType] : this.actionTypes(),
            transactionSearch.search,
            transactionSearch.created,
        );
    }

    protected async exportXlsx(): Promise<void> {
        const portfolio = await this.portfolioService.getCurrentPortfolio();
        const transactionSearch = this.transactionSearch();
        await this.transactionService.exportXlsx(
            portfolio.id,
            this.assetId(),
            transactionSearch.selectedType !== null ? [transactionSearch.selectedType] : this.actionTypes(),
            transactionSearch.search,
            transactionSearch.created,
        );
    }

    protected readonly TransactionActionType = TransactionActionType;
    protected readonly TransactionGridColumnEnum = TransactionGridColumnEnum;
}
