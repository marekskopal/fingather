import { ChangeDetectionStrategy, Component, OnInit } from '@angular/core';
import { Currency, PortfolioData } from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { CurrencyService, PortfolioDataService, PortfolioService } from '@app/services';
import { first } from 'rxjs/operators';

@Component({
    selector: 'fingather-portfolio-total',
    templateUrl: 'portfolio-total.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PortfolioTotalComponent implements OnInit {
    public portfolioData: PortfolioData | null;
    public defaultCurrency: Currency;

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

    public async refreshPortfolioData(): Promise<void> {
        this.portfolioData = null;

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        this.portfolioDataService.getPortfolioData(portfolio.id)
            .pipe(first())
            .subscribe((portfolioData) => {
                this.portfolioData = portfolioData;
            });
    }

    protected readonly PortfolioDataRangeEnum = RangeEnum;
}
