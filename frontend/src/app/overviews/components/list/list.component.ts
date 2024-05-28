import { ChangeDetectionStrategy, Component, OnInit } from '@angular/core';
import { Currency, YearCalculatedData } from '@app/models';
import { ModeEnum } from '@app/overviews/components/list/enum/mode-enum';
import { CurrencyService, OverviewService, PortfolioService } from '@app/services';

@Component({
    templateUrl: './list.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ListComponent implements OnInit {
    public yearCalculatedDatas: YearCalculatedData[] | null = null;
    public defaultCurrency: Currency;

    protected readonly ModeEnum = ModeEnum;
    protected mode: ModeEnum = ModeEnum.Interannually;

    public constructor(
        private readonly overviewService: OverviewService,
        private readonly currencyService: CurrencyService,
        private readonly portfolioService: PortfolioService,
    ) {
    }

    public async ngOnInit(): Promise<void> {
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.refreshYearCalculatedData();

        this.portfolioService.eventEmitter.subscribe(() => {
            this.refreshYearCalculatedData();
        });
    }

    public async refreshYearCalculatedData(): Promise<void> {
        this.yearCalculatedDatas = null;

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        this.yearCalculatedDatas = await this.overviewService.getYearCalculatedData(portfolio.id);
    }

    public changeMode(): void {
        this.mode = this.mode === ModeEnum.Interannually ? ModeEnum.Total : ModeEnum.Interannually;
    }
}
