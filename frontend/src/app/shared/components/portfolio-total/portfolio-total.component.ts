import {AsyncPipe, DecimalPipe} from "@angular/common";
import {
    ChangeDetectionStrategy, Component, inject, input, OnInit, signal,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {Currency, Portfolio, PortfolioData} from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { CurrencyService, PortfolioDataService, PortfolioService } from '@app/services';
import {HelpComponent} from "@app/shared/components/help/help.component";
import {
    PortfolioValueChartComponent,
} from "@app/shared/components/portfolio-value-chart/portfolio-value-chart.component";
import {ValueIconComponent} from "@app/shared/components/value-icon/value-icon.component";
import {ColoredValueDirective} from "@app/shared/directives/colored-value.directive";
import {MoneyPipe} from "@app/shared/pipes/money.pipe";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    selector: 'fingather-portfolio-total',
    templateUrl: 'portfolio-total.component.html',
    imports: [
        MatIcon,
        PortfolioValueChartComponent,
        DecimalPipe,
        MoneyPipe,
        AsyncPipe,
        TranslatePipe,
        ValueIconComponent,
        ColoredValueDirective,
        HelpComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PortfolioTotalComponent implements OnInit {
    private readonly portfolioDataService = inject(PortfolioDataService);
    private readonly currencyService = inject(CurrencyService);
    private readonly portfolioService = inject(PortfolioService);

    public readonly $portfolio = input<Portfolio | null>(null, {
        alias: 'portfolio',
    })

    private readonly $portfolioData = signal<PortfolioData | null>(null);
    protected defaultCurrency: Currency;

    public async ngOnInit(): Promise<void> {
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.refreshPortfolioData();

        this.portfolioService.subscribe(() => {
            this.refreshPortfolioData();
        });
    }

    protected get portfolioData(): PortfolioData | null {
        return this.$portfolioData();
    }

    public async refreshPortfolioData(): Promise<void> {
        this.$portfolioData.set(null);

        const portfolio = this.$portfolio() ?? await this.portfolioService.getCurrentPortfolio();

        const portfolioData = await this.portfolioDataService.getPortfolioData(portfolio.id);
        this.$portfolioData.set(portfolioData);
    }

    protected readonly PortfolioDataRangeEnum = RangeEnum;
    protected readonly parseFloat = parseFloat;
}
