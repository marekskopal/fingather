import {AsyncPipe} from "@angular/common";
import {
    ChangeDetectionStrategy, Component, ElementRef, inject, input, OnInit, signal,
} from '@angular/core';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from "@angular/forms";
import {Ticker} from "@app/models";
import {TickerService} from "@app/services";
import {SearchHighlightComponent} from "@app/shared/components/search-highlight/search-highlight.component";
import {SearchInputComponent} from "@app/shared/components/search-input/search-input.component";
import {CurrencyCodePipe} from "@app/shared/pipes/currency-code.pipe";
import {NgbDropdown, NgbDropdownItem, NgbDropdownMenu, NgbDropdownToggle} from "@ng-bootstrap/ng-bootstrap";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    selector: 'fingather-ticker-search-selector',
    templateUrl: 'ticker-search-selector.component.html',
    imports: [
        NgbDropdown,
        NgbDropdownToggle,
        NgbDropdownMenu,
        NgbDropdownItem,
        CurrencyCodePipe,
        AsyncPipe,
        TranslatePipe,
        SearchInputComponent,
        SearchHighlightComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        {
            provide: NG_VALUE_ACCESSOR,
            multi: true,
            useExisting: TickerSearchSelectorComponent,
        },
    ],
})
export class TickerSearchSelectorComponent implements ControlValueAccessor, OnInit {
    private readonly tickerService = inject(TickerService);
    private readonly elementRef = inject(ElementRef);

    public readonly $id = input.required<string>({
        alias: 'id',
    });
    public readonly $placeholder = input<string>('', {
        alias: 'placeholder',
    });

    protected readonly $tickers = signal<Ticker[]>([])

    protected readonly $search = signal<string | null>(null);

    protected value: Ticker | null = null;
    private touched: boolean = false;
    protected disabled: boolean = false;

    //eslint-disable-next-line unused-imports/no-unused-vars
    private onChange = (value: number | null): void => {};
    private onTouched = (): void => {};

    public writeValue(value: Ticker | null): void {
        this.value = value;
    }

    public registerOnChange(onChange: any): void {
        this.onChange = onChange;
    }

    public registerOnTouched(onTouched: any): void {
        this.onTouched = onTouched;
    }

    public setDisabledState(disabled: boolean): void {
        this.disabled = disabled;
    }

    public ngOnInit(): void {
        this.onSearchKeyUp(null);
    }

    protected onSelect(value: Ticker | null): void {
        this.value = value;

        this.onChange(value?.id ?? null);
        this.onTouched();

        this.markAsTouched();
    }

    protected async onSearchKeyUp(search: string | null): Promise<void> {
        this.$search.set(search);
        const tickers = search === null || search === '' ?
            await this.tickerService.getTickersMostUsed(20) :
            await this.tickerService.getTickers(search, 20);
        this.$tickers.set(tickers);
    }

    protected onOpenChange(open: boolean): void {
        if (!open) {
            return;
        }

        const searchInput = this.elementRef.nativeElement.querySelector('input');

        setTimeout(() => {
            searchInput.focus();
        }, 10);
    }

    private markAsTouched(): void {
        if (this.touched) {
            return;
        }
        this.touched = true;
    }
}
