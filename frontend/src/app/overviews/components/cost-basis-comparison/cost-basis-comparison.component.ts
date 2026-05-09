import { AsyncPipe, DecimalPipe } from '@angular/common';
import {
    ChangeDetectionStrategy,
    Component,
    DestroyRef,
    inject,
    OnInit,
    signal,
} from '@angular/core';
import { MatIcon } from '@angular/material/icon';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { Currency } from '@app/models';
import { CostBasisComparison } from '@app/models/cost-basis-comparison';
import { CostBasisComparisonService, CurrencyService, PortfolioService } from '@app/services';
import { PortfolioSelectorComponent } from '@app/shared/components/portfolio-selector/portfolio-selector.component';
import { TableValueComponent } from '@app/shared/components/table-value/table-value.component';
import { MoneyPipe } from '@app/shared/pipes/money.pipe';
import { TranslatePipe } from '@ngx-translate/core';

@Component({
    templateUrl: './cost-basis-comparison.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        TranslatePipe,
        PortfolioSelectorComponent,
        TableValueComponent,
        DecimalPipe,
        MoneyPipe,
        AsyncPipe,
        RouterLink,
        MatIcon,
    ],
})
export class CostBasisComparisonComponent implements OnInit {
    private readonly comparisonService = inject(CostBasisComparisonService);
    private readonly currencyService = inject(CurrencyService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly destroyRef = inject(DestroyRef);
    private readonly route = inject(ActivatedRoute);

    protected readonly comparison = signal<CostBasisComparison | null>(null);
    protected defaultCurrency: Currency;
    protected year: number;

    public async ngOnInit(): Promise<void> {
        this.year = Number(this.route.snapshot.paramMap.get('year'));
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();
        this.refresh();

        this.portfolioService.subscribe(() => {
            this.refresh();
        }, this.destroyRef);
    }

    private async refresh(): Promise<void> {
        this.comparison.set(null);
        const portfolio = await this.portfolioService.getCurrentPortfolio();
        const comparison = await this.comparisonService.getCostBasisComparison(portfolio.id, this.year);
        this.comparison.set(comparison);
    }
}
