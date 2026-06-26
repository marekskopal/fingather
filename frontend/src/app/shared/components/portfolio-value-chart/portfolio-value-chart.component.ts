import {
    ChangeDetectionStrategy,
    Component, CSP_NONCE, DestroyRef, inject, input, OnChanges, OnInit, signal,
} from '@angular/core';
import {Portfolio, PortfolioDataWithBenchmarkData} from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { CurrencyService, PortfolioDataService, PortfolioService } from '@app/services';
import {ChartUtils} from "@app/utils/chart-utils";
import { TranslateService } from '@ngx-translate/core';
import {
    ApexAxisChartSeries, ApexChart, ApexDataLabels, ApexFill,
    ApexGrid, ApexLegend, ApexStroke, ApexTheme, ApexTooltip, ApexXAxis, ApexYAxis, NgApexchartsModule,
} from 'ng-apexcharts';

export type ChartOptions = {
    series: ApexAxisChartSeries;
    chart: ApexChart;
    xaxis: ApexXAxis;
    yaxis: ApexYAxis;
    dataLabels: ApexDataLabels;
    grid: ApexGrid;
    stroke: ApexStroke;
    legend: ApexLegend;
    theme: ApexTheme;
    fill: ApexFill;
    tooltip: ApexTooltip;
    colors: string[];
};

@Component({
    templateUrl: 'portfolio-value-chart.component.html',
    selector: 'fingather-portfolio-value-chart',
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        NgApexchartsModule,
    ],
})
export class PortfolioValueChartComponent implements OnInit, OnChanges {
    private readonly portfolioDataService = inject(PortfolioDataService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly currencyService = inject(CurrencyService);
    private readonly nonce = inject(CSP_NONCE);
    private readonly destroyRef = inject(DestroyRef);
    private readonly translateService = inject(TranslateService);

    public readonly range = input.required<RangeEnum>();
    public readonly customRangeFrom = input<string | null>(null);
    public readonly customRangeTo = input<string | null>(null);
    public readonly portfolio = input<Portfolio | null>(null);
    public readonly benchmarkAssetId = input<number | null>(null);
    public readonly benchmarkTickerId = input<number | null>(null);
    public readonly height = input<string>('auto');
    public readonly showLabels = input<boolean>(true);
    public readonly sparkline = input<boolean>(false);

    protected chartOptions: ChartOptions;
    protected readonly loading = signal<boolean>(false);

    public ngOnInit(): void {
        this.initializeChartOptions();

        this.portfolioService.subscribe(() => {
            this.refreshChart();
        }, this.destroyRef);
    }

    public ngOnChanges(): void {
        this.initializeBenchmarkChartOptions();
        this.refreshChart();
    }

    private async refreshChart(): Promise<void> {
        this.loading.set(true);

        const portfolio = this.portfolio() ?? await this.portfolioService.getCurrentPortfolio();

        const [portfolioData, currencies] = await Promise.all([
            this.portfolioDataService.getPortfolioDataRange(
                portfolio.id,
                this.range(),
                this.benchmarkAssetId(),
                this.benchmarkTickerId(),
                this.customRangeFrom(),
                this.customRangeTo(),
            ),
            this.currencyService.getCurrenciesMap(),
        ]);

        const currencySymbol = currencies.get(portfolio.currencyId)?.symbol ?? '';
        const formatter = ChartUtils.currencyFormatter(currencySymbol);
        this.chartOptions.yaxis = ChartUtils.yAxis(this.showLabels(), formatter);
        this.chartOptions.tooltip = { y: { formatter } };

        const chartMap = this.mapChart(portfolioData);
        this.chartOptions.xaxis.categories = chartMap.categories;
        this.chartOptions.series[0].data = chartMap.valueSeries;
        this.chartOptions.series[1].data = chartMap.investedValueSeries;
        if (chartMap.benchmarkSeries.length > 0) {
            this.chartOptions.series[2].data = chartMap.benchmarkSeries;
        }
        this.loading.set(false);
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [
                {
                    name: this.translateService.instant('app.shared.charts.seriesValue'),
                    data: [],
                    zIndex: 3,
                },
                {
                    name: this.translateService.instant('app.shared.charts.seriesInvestedValue'),
                    data: [],
                    zIndex: 1,
                },
            ],
            chart: {
                ...ChartUtils.locale(this.translateService.currentLang() ?? 'en'),
                height: this.height(),
                type: 'area',
                zoom: {
                    enabled: false,
                },
                toolbar: {
                    show: false,
                },
                sparkline: {
                    enabled: this.sparkline(),
                },
                animations: {
                    enabled: false,
                },
                nonce: this.nonce ?? undefined,
            },
            dataLabels: {
                enabled: false,
            },
            stroke: {
                curve: 'straight',
                width: 3,
            },
            grid: ChartUtils.grid(),
            xaxis: ChartUtils.xAxis(this.showLabels()),
            yaxis: ChartUtils.yAxis(this.showLabels()),
            legend: {
                show: false,
            },
            theme: ChartUtils.theme(),
            fill: ChartUtils.gradientFill(),
            tooltip: {},
            colors: ChartUtils.colors(3),
        };

        if (this.benchmarkAssetId() !== null || this.benchmarkTickerId() !== null) {
            this.chartOptions.series[2] = {
                name: this.translateService.instant('app.shared.charts.seriesBenchmark'),
                data: [],
            };
        }
    }

    private initializeBenchmarkChartOptions(): void {
        if (this.benchmarkAssetId() === null && this.benchmarkTickerId() === null) {
            return;
        }

        this.chartOptions.series[2] = {
            name: this.translateService.instant('app.shared.charts.seriesBenchmark'),
            data: [],
            zIndex: 2,
        };
    }

    private mapChart(
        portfolioDatas: PortfolioDataWithBenchmarkData[],
    ): {
            valueSeries: number[],
            investedValueSeries: number[],
            benchmarkSeries: number[],
            categories: string[]
        } {
        return {
            valueSeries: portfolioDatas.map((portfolioData) => parseFloat(portfolioData.value)),
            investedValueSeries: portfolioDatas.map(
                (portfolioData) => parseFloat(portfolioData.transactionValue),
            ),
            benchmarkSeries: (this.benchmarkAssetId() !== null || this.benchmarkTickerId() !== null)
                ? portfolioDatas.map((portfolioData) => parseFloat(portfolioData.benchmarkData?.value ?? '0.0'))
                : [],
            categories: portfolioDatas.map((portfolioData) => portfolioData.date),
        };
    }
}
