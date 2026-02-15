import {AsyncPipe, DecimalPipe} from '@angular/common';
import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit, signal,
} from '@angular/core';
import {MatIcon} from '@angular/material/icon';
import {ActivatedRoute, RouterLink} from '@angular/router';
import {Currency, TaxReport} from '@app/models';
import {CurrencyService, PortfolioService, TaxReportService} from '@app/services';
import {PortfolioSelectorComponent} from '@app/shared/components/portfolio-selector/portfolio-selector.component';
import {TableValueComponent} from '@app/shared/components/table-value/table-value.component';
import {ValueIconComponent} from '@app/shared/components/value-icon/value-icon.component';
import {MoneyPipe} from '@app/shared/pipes/money.pipe';
import {ScrollShadowDirective} from '@marekskopal/ng-scroll-shadow';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    templateUrl: './tax-report.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        TranslatePipe,
        PortfolioSelectorComponent,
        TableValueComponent,
        ValueIconComponent,
        DecimalPipe,
        MoneyPipe,
        AsyncPipe,
        ScrollShadowDirective,
        RouterLink,
        MatIcon,
    ],
})
export class TaxReportComponent implements OnInit {
    private readonly taxReportService = inject(TaxReportService);
    private readonly currencyService = inject(CurrencyService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly changeDetectorRef = inject(ChangeDetectorRef);
    private readonly route = inject(ActivatedRoute);

    protected readonly taxReport = signal<TaxReport | null>(null);
    protected defaultCurrency: Currency;
    protected year: number;

    public async ngOnInit(): Promise<void> {
        this.year = Number(this.route.snapshot.paramMap.get('year'));
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.refreshTaxReport();

        this.portfolioService.subscribe(() => {
            this.refreshTaxReport();
            this.changeDetectorRef.detectChanges();
        });
    }

    protected async exportXlsx(): Promise<void> {
        const portfolio = await this.portfolioService.getCurrentPortfolio();
        await this.taxReportService.exportXlsx(portfolio.id, this.year);
    }

    protected async exportPdf(): Promise<void> {
        const portfolio = await this.portfolioService.getCurrentPortfolio();
        await this.taxReportService.exportPdf(portfolio.id, this.year);
    }

    private async refreshTaxReport(): Promise<void> {
        this.taxReport.set(null);

        const portfolio = await this.portfolioService.getCurrentPortfolio();
        const taxReport = await this.taxReportService.getTaxReport(portfolio.id, this.year);
        this.taxReport.set(taxReport);
    }
}
