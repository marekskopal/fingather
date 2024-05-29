import {
    ChangeDetectionStrategy, Component, OnInit, signal, WritableSignal
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
    private readonly $portfolioData: WritableSignal<PortfolioData | null> = signal<PortfolioData | null>(null);
    protected defaultCurrency: Currency;

    public constructor(
        private readonly portfolioDataService: PortfolioDataService,
        private readonly currencyService: CurrencyService,
        private readonly portfolioService: PortfolioService,
    ) { }

    public async ngOnInit(): Promise<void> {
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.refreshPortfolioData();

        this.portfolioService.eventEmitter.subscribe(() => {
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
}
