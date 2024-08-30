import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit, signal
} from '@angular/core';
import { Currency, YearCalculatedData } from '@app/models';
import { ModeEnum } from '@app/overviews/components/list/enum/mode-enum';
import { CurrencyService, OverviewService, PortfolioService } from '@app/services';

@Component({
    templateUrl: './list.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ListComponent implements OnInit {
    private readonly overviewService = inject(OverviewService);
    private readonly currencyService = inject(CurrencyService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly changeDetectorRef = inject(ChangeDetectorRef);

    private readonly $yearCalculatedDatas = signal<YearCalculatedData[] | null>(null);
    protected defaultCurrency: Currency;

    protected readonly ModeEnum = ModeEnum;
    protected mode: ModeEnum = ModeEnum.Interannually;

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
