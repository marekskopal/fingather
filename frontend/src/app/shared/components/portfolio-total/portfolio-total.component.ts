import {
    ChangeDetectionStrategy, Component, inject, OnInit, signal, WritableSignal
} from '@angular/core';
import { Currency, PortfolioData } from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { CurrencyService, PortfolioDataService, PortfolioService } from '@app/services';

@Component({
    selector: 'fingather-portfolio-total',
    templateUrl: 'portfolio-total.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PortfolioTotalComponent implements OnInit {
    private readonly portfolioDataService = inject(PortfolioDataService);
    private readonly currencyService = inject(CurrencyService);
    private readonly portfolioService = inject(PortfolioService);

    private readonly $portfolioData: WritableSignal<PortfolioData | null> = signal<PortfolioData | null>(null);
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

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const portfolioData = await this.portfolioDataService.getPortfolioData(portfolio.id);
        this.$portfolioData.set(portfolioData);
    }

    protected readonly PortfolioDataRangeEnum = RangeEnum;
    protected readonly parseFloat = parseFloat;
}
