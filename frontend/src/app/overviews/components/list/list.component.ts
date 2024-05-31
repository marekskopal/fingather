import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit, signal
} from '@angular/core';
import { Currency, YearCalculatedData } from '@app/models';
import { ModeEnum } from '@app/overviews/components/list/enum/mode-enum';
import { CurrencyService, OverviewService, PortfolioService } from '@app/services';

@Component({
    templateUrl: './list.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ListComponent implements OnInit {
    private readonly $yearCalculatedDatas = signal<YearCalculatedData[] | null>(null);
    protected defaultCurrency: Currency;

    protected readonly ModeEnum = ModeEnum;
    protected mode: ModeEnum = ModeEnum.Interannually;

    public constructor(
        private readonly overviewService: OverviewService,
        private readonly currencyService: CurrencyService,
        private readonly portfolioService: PortfolioService,
        private readonly changeDetectorRef: ChangeDetectorRef,
    ) {
    }

    public async ngOnInit(): Promise<void> {
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.refreshYearCalculatedData();

        this.portfolioService.subscribe(() => {
            this.refreshYearCalculatedData();
            this.changeDetectorRef.detectChanges();
        });
    }

    protected get yearCalculatedDatas(): YearCalculatedData[] | null {
        return this.$yearCalculatedDatas();
    }

    private async refreshYearCalculatedData(): Promise<void> {
        this.$yearCalculatedDatas.set(null);

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const yearCalculatedDatas = await this.overviewService.getYearCalculatedData(portfolio.id);
        this.$yearCalculatedDatas.set(yearCalculatedDatas);
    }

    protected changeMode(): void {
        this.mode = this.mode === ModeEnum.Interannually ? ModeEnum.Total : ModeEnum.Interannually;
    }
}
