import {
    ChangeDetectionStrategy, Component, output,
} from '@angular/core';
import { TransactionActionType } from '@app/models';
import {TransactionSearch} from "@app/transactions/types/transaction-search";

@Component({
    selector: 'fingather-transactions-search',
    templateUrl: './search.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SearchComponent {
    public readonly onSearch$ = output<TransactionSearch>({
        alias: 'onSearch',
    });

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

    protected dateClick(event: Event): void {
        const element = event.target as HTMLElement;
        let input: HTMLInputElement = element as HTMLInputElement;
        if (element.tagName !== 'INPUT') {
            input = element.getElementsByTagName('input')[0];
        }

        console.log(element);


        input.showPicker();
    }

    private handleOnSearch(): void {
        this.onSearch$.emit({
            search: this.search,
            selectedType: this.selectedType,
            created: this.created,
        });
    }
}
