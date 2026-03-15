import { DecimalPipe, NgClass } from '@angular/common';
import {
    ChangeDetectionStrategy, Component, inject, input, OnInit, signal,
} from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Currency, StrategyRebalancing } from '@app/models';
import { CurrencyService, StrategyRebalancingService } from '@app/services';
import { HelpComponent } from '@app/shared/components/help/help.component';
import { SelectComponent } from '@app/shared/components/select/select.component';
import { SelectItem } from '@app/shared/types/select-item';
import { TranslatePipe } from '@ngx-translate/core';

@Component({
    selector: 'fingather-strategy-rebalancing',
    templateUrl: 'strategy-rebalancing.component.html',
    imports: [
        TranslatePipe,
        DecimalPipe,
        NgClass,
        FormsModule,
        SelectComponent,
        HelpComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class StrategyRebalancingComponent implements OnInit {
    private readonly strategyRebalancingService = inject(StrategyRebalancingService);
    private readonly currencyService = inject(CurrencyService);

    public readonly strategyId = input.required<number>();

    protected readonly cashToInvest = signal<string>('0');
    protected readonly allowSelling = signal<boolean>(false);
    protected readonly loading = signal<boolean>(false);
    protected readonly result = signal<StrategyRebalancing | null>(null);
    protected readonly currencies = signal<SelectItem<number, string>[]>([]);
    protected readonly selectedCurrencyId = signal<number | null>(null);

    public async ngOnInit(): Promise<void> {
        const [allCurrencies, defaultCurrency] = await Promise.all([
            this.currencyService.getCurrencies(),
            this.currencyService.getDefaultCurrency(),
        ]);

        this.currencies.set(allCurrencies.map((c: Currency) => ({ key: c.id, label: `${c.code} (${c.symbol})` })));
        this.selectedCurrencyId.set(defaultCurrency.id);
    }

    protected async calculate(): Promise<void> {
        this.loading.set(true);
        this.result.set(null);

        const rebalancing = await this.strategyRebalancingService.calculate(this.strategyId(), {
            cashToInvest: this.cashToInvest(),
            cashCurrencyId: this.selectedCurrencyId(),
            allowSelling: this.allowSelling(),
        });

        this.result.set(rebalancing);
        this.loading.set(false);
    }

    protected isBuy(value: string): boolean {
        return parseFloat(value) > 0;
    }

    protected isSell(value: string): boolean {
        return parseFloat(value) < 0;
    }
}
