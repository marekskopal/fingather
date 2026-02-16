import { AsyncPipe, DatePipe, DecimalPipe } from '@angular/common';
import {
    ChangeDetectionStrategy, Component, computed, inject, OnInit, signal,
} from '@angular/core';
import { Currency, DividendCalendarItem } from '@app/models';
import { CurrencyService, DividendCalendarService, PortfolioService } from '@app/services';
import {TickerLogoComponent} from "@app/shared/components/ticker-logo/ticker-logo.component";
import {MoneyPipe} from "@app/shared/pipes/money.pipe";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";
import { TranslatePipe } from '@ngx-translate/core';

interface MonthGroup {
    label: string;
    items: DividendCalendarItem[];
    total: number;
}

@Component({
    selector: 'fingather-dividend-forecast-calendar',
    templateUrl: 'dividend-forecast-calendar.component.html',
    imports: [
        AsyncPipe,
        DatePipe,
        DecimalPipe,
        MoneyPipe,
        TranslatePipe,
        TickerLogoComponent,
        ScrollShadowDirective,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DividendForecastCalendarComponent implements OnInit {
    private readonly dividendCalendarService = inject(DividendCalendarService);
    private readonly currencyService = inject(CurrencyService);
    private readonly portfolioService = inject(PortfolioService);

    protected readonly loading = signal<boolean>(false);
    protected readonly items = signal<DividendCalendarItem[]>([]);
    protected readonly defaultCurrency = signal<Currency | null>(null);

    protected readonly monthGroups = computed<MonthGroup[]>(() => {
        const grouped = new Map<string, DividendCalendarItem[]>();

        for (const item of this.items()) {
            const date = new Date(item.exDate);
            const key = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
            const group = grouped.get(key);
            if (group !== undefined) {
                group.push(item);
            } else {
                grouped.set(key, [item]);
            }
        }

        const result: MonthGroup[] = [];
        for (const [key, groupItems] of grouped) {
            const [year, month] = key.split('-');
            const date = new Date(parseInt(year, 10), parseInt(month, 10) - 1, 1);
            const label = date.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
            const total = groupItems.reduce((sum, item) => sum + parseFloat(item.totalAmountDefaultCurrency), 0);
            result.push({ label, items: groupItems, total });
        }

        return result;
    });

    public ngOnInit(): void {
        this.loadData();

        this.portfolioService.subscribe(() => {
            this.loadData();
        });
    }

    private async loadData(): Promise<void> {
        this.loading.set(true);

        this.defaultCurrency.set(await this.currencyService.getDefaultCurrency());

        const portfolio = await this.portfolioService.getCurrentPortfolio();
        const items = await this.dividendCalendarService.getDividendCalendar(portfolio.id);
        this.items.set(items);

        this.loading.set(false);
    }
}
