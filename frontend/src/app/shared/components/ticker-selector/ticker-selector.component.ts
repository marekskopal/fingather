import {
    ChangeDetectionStrategy, Component, input, OnInit, output, signal,
} from '@angular/core';
import {Ticker} from "@app/models";
import {NgbDropdown, NgbDropdownItem, NgbDropdownMenu, NgbDropdownToggle} from "@ng-bootstrap/ng-bootstrap";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    selector: 'fingather-ticker-selector',
    templateUrl: 'ticker-selector.component.html',
    imports: [
        NgbDropdown,
        NgbDropdownToggle,
        NgbDropdownMenu,
        NgbDropdownItem,
        TranslateModule,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TickerSelectorComponent implements OnInit {
    public readonly $tickers = input.required<Ticker[]>({
        alias: 'tickers',
    });
    public readonly $selectedTickerId = input<number | null>(null, {
        alias: 'selectedTickerId',
    });
    public readonly $placeholder = input<string | null>(null, {
        alias: 'placeholder',
    });
    public readonly onChangeTicker$ = output<Ticker>({
        alias: 'onChangeTicker',
    });

    public ngOnInit(): void {
        this.$selectedTicker.set(this.getTickerById(this.$selectedTickerId()));
    }

    protected readonly $selectedTicker = signal<Ticker | null>(null);

    protected changeTicker(ticker: Ticker): void {
        this.$selectedTicker.set(ticker);

        this.onChangeTicker$.emit(ticker);
    }

    private getTickerById(id: number | null): Ticker | null {
        return this.$tickers().find((ticker) => ticker.id === id) ?? null;
    }
}
