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
import { RouterLink } from '@angular/router';
import { Currency } from '@app/models';
import { TaxOptimization, TaxOptimizationSuggestion } from '@app/models/tax-optimization';
import { CurrencyService, PortfolioService, TaxOptimizationService } from '@app/services';
import { AssetDisplayComponent } from '@app/shared/components/asset-display/asset-display.component';
import { PortfolioSelectorComponent } from '@app/shared/components/portfolio-selector/portfolio-selector.component';
import { TableValueComponent } from '@app/shared/components/table-value/table-value.component';
import { MoneyPipe } from '@app/shared/pipes/money.pipe';
import { ScrollShadowDirective } from '@marekskopal/ng-scroll-shadow';
import { TranslatePipe } from '@ngx-translate/core';

@Component({
    templateUrl: './tax-optimization.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        TranslatePipe,
        PortfolioSelectorComponent,
        AssetDisplayComponent,
        TableValueComponent,
        DecimalPipe,
        MoneyPipe,
        AsyncPipe,
        ScrollShadowDirective,
        RouterLink,
        MatIcon,
    ],
})
export class TaxOptimizationComponent implements OnInit {
    private readonly taxOptimizationService = inject(TaxOptimizationService);
    private readonly currencyService = inject(CurrencyService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly destroyRef = inject(DestroyRef);

    protected readonly optimization = signal<TaxOptimization | null>(null);
    protected defaultCurrency: Currency;

    public async ngOnInit(): Promise<void> {
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();
        this.refresh();

        this.portfolioService.subscribe(() => {
            this.refresh();
        }, this.destroyRef);
    }

    protected isCzech(optimization: TaxOptimization): boolean {
        return optimization.jurisdiction === 'CzechRepublic';
    }

    protected trackBySuggestion(_index: number, suggestion: TaxOptimizationSuggestion): number {
        return suggestion.assetId;
    }

    private async refresh(): Promise<void> {
        this.optimization.set(null);
        const portfolio = await this.portfolioService.getCurrentPortfolio();
        const optimization = await this.taxOptimizationService.getTaxOptimization(portfolio.id);
        this.optimization.set(optimization);
    }
}
