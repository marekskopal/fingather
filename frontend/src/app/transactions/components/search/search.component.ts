import {
    ChangeDetectionStrategy, Component, output,
} from '@angular/core';
import { TransactionActionType } from '@app/models';
import {DateInputComponent} from "@app/shared/components/date-input/date-input.component";
import {SearchInputComponent} from "@app/shared/components/search-input/search-input.component";
import {TagComponent} from "@app/shared/components/tag/tag.component";
import {TransactionSearch} from "@app/transactions/types/transaction-search";
import {NgbDropdown, NgbDropdownItem, NgbDropdownMenu, NgbDropdownToggle} from "@ng-bootstrap/ng-bootstrap";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    selector: 'fingather-transactions-search',
    templateUrl: './search.component.html',
    imports: [
        TranslatePipe,
        SearchInputComponent,
        NgbDropdown,
        NgbDropdownToggle,
        NgbDropdownMenu,
        NgbDropdownItem,
        TagComponent,
        DateInputComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SearchComponent {
    public readonly afterSearch = output<TransactionSearch>();

    protected readonly transactionActionTypes = [
        TransactionActionType.Buy,
        TransactionActionType.Sell,
        TransactionActionType.Dividend,
        TransactionActionType.Tax,
        TransactionActionType.Fee,
        TransactionActionType.DividendTax,
    ];

    protected search: string | null = null;
    protected selectedType: TransactionActionType | null = null;
    protected created: string | null = null;

    protected searchTransactions(value: string | null): void {
        this.search = value;

        this.handleOnSearch();
    }

    protected selectType(type: TransactionActionType | null): void {
        this.selectedType = type;

        this.handleOnSearch();
    }

    protected selectCreated(created: Event): void {
        const input = created.target as HTMLInputElement;
        this.created = input.value;
        if (input.value === '') {
            this.created = null;
        }

        this.handleOnSearch();
    }

    private handleOnSearch(): void {
        this.afterSearch.emit({
            search: this.search,
            selectedType: this.selectedType,
            created: this.created,
        });
    }
}
