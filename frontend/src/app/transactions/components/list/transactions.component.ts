import {DatePipe} from "@angular/common";
import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit, signal
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import {TransactionActionType} from "@app/models";
import { TransactionList } from '@app/models/transaction-list';
import { PortfolioService, TransactionService } from '@app/services';
import {DeleteButtonComponent} from "@app/shared/components/delete-button/delete-button.component";
import {PaginationComponent} from "@app/shared/components/pagination/pagination.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {TagComponent} from "@app/shared/components/tag/tag.component";
import {TickerLogoComponent} from "@app/shared/components/ticker-logo/ticker-logo.component";
import {TableGridDirective} from "@app/shared/directives/table-grid.directive";
import {TableGridColumn} from "@app/shared/types/table-grid-column";
import {SearchComponent} from "@app/transactions/components/search/search.component";
import {TransactionListComponent} from "@app/transactions/components/transaction-list/transaction-list.component";
import {TransactionSearch} from "@app/transactions/types/transaction-search";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: './transactions.component.html',
    standalone: true,
    imports: [
        PortfolioSelectorComponent,
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
        TransactionListComponent
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TransactionsComponent implements OnInit {
    private readonly transactionService = inject(TransactionService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly changeDetectorRef = inject(ChangeDetectorRef);

    private page: number = 1;
    public pageSize: number = 50;

    private readonly $transactionList = signal<TransactionList | null>(null);

    private readonly $transactionSearch = signal<TransactionSearch>({
        search: null,
        selectedType: null,
        created: null,
    });

    protected readonly tableGridColumns: TableGridColumn[] = [
        { min: '130px', max: '1.2fr' },
        { min: '130px', max: '1.2fr' },
        { min: '130px', max: '1.2fr' },
        { min: '250px', max: '3fr' },
        { min: '124px', max: '1fr' },
    ];

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

    protected readonly TransactionActionType = TransactionActionType;
}
