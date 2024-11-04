import {AsyncPipe, DecimalPipe} from "@angular/common";
import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit, signal
} from '@angular/core';
import { Currency, YearCalculatedData } from '@app/models';
import { ModeEnum } from '@app/overviews/components/list/enum/mode-enum';
import { CurrencyService, OverviewService, PortfolioService } from '@app/services';
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {TableValueComponent} from "@app/shared/components/table-value/table-value.component";
import {MoneyPipe} from "@app/shared/pipes/money.pipe";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: './list.component.html',
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        TranslatePipe,
        PortfolioSelectorComponent,
        TableValueComponent,
        DecimalPipe,
        MoneyPipe,
        AsyncPipe,
        ScrollShadowDirective
    ]
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
