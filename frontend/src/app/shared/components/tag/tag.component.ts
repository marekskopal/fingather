import {
    ChangeDetectionStrategy, Component, computed, input,
} from '@angular/core';
import {TransactionActionType} from "@app/models";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    selector: 'fingather-tag',
    templateUrl: 'tag.component.html',
    imports: [
        TranslateModule,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TagComponent {
    public readonly $type = input.required<TransactionActionType>({
        alias: 'type',
    });

    protected readonly $class = computed<string>(() => {
        switch (this.$type()) {
            case TransactionActionType.Buy:
                return 'tag-buy';
            case TransactionActionType.Sell:
                return 'tag-sell';
            case TransactionActionType.Dividend:
                return 'tag-dividend';
            case TransactionActionType.Tax:
                return 'tag-tax';
            case TransactionActionType.Fee:
                return 'tag-fee';
            case TransactionActionType.DividendTax:
                return 'tag-dividend-tax';
            default:
                return 'tag-undefined';
        }
    });

    protected readonly $text = computed<string>(() => {
        switch (this.$type()) {
            case TransactionActionType.Buy:
                return 'buy';
            case TransactionActionType.Sell:
                return 'sell';
            case TransactionActionType.Dividend:
                return 'dividend';
            case TransactionActionType.Tax:
                return 'tax';
            case TransactionActionType.Fee:
                return 'fee';
            case TransactionActionType.DividendTax:
                return 'dividendTax';
            default:
                return 'undefined';
        }
    });
}
