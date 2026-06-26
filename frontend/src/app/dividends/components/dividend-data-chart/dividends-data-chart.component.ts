import {
    ChangeDetectionStrategy,
    Component, CSP_NONCE, DestroyRef, inject, input, InputSignal, OnChanges, OnInit, signal,
} from '@angular/core';
import {
    DividendDataDateInterval,
} from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { CurrencyService, DividendDataService, PortfolioService } from '@app/services';
import {ChartUtils} from "@app/utils/chart-utils";
import { TranslateService } from '@ngx-translate/core';
import {
    ApexAxisChartSeries, ApexChart, ApexDataLabels, ApexFill, ApexGrid, ApexLegend, ApexPlotOptions,
    ApexTheme, ApexTooltip, ApexXAxis, ApexYAxis, NgApexchartsModule,
} from 'ng-apexcharts';

export type ChartOptions = {
    series: ApexAxisChartSeries;
    chart: ApexChart;
    plotOptions: ApexPlotOptions;
    dataLabels: ApexDataLabels;
    xaxis: ApexXAxis;
    yaxis: ApexYAxis;
    legend: ApexLegend;
    theme: ApexTheme;
    fill: ApexFill;
    grid: ApexGrid;
    tooltip: ApexTooltip;
    colors: string[];
};

@Component({
    templateUrl: 'dividends-data-chart.component.html',
    selector: 'fingather-dividends-data-chart',
    imports: [
        NgApexchartsModule,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DividendsDataChartComponent implements OnInit, OnChanges {
    private readonly dividendDataService = inject(DividendDataService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly currencyService = inject(CurrencyService);
    private readonly nonce = inject(CSP_NONCE);
    private readonly destroyRef = inject(DestroyRef);
    private readonly translateService = inject(TranslateService);

    public readonly range: InputSignal<RangeEnum> = input.required<RangeEnum>();
    public chartOptions: ChartOptions;
    protected readonly loading = signal<boolean>(false);

    public ngOnInit(): void {
        this.refreshChart();

        this.portfolioService.subscribe(() => {
            this.refreshChart();
        }, this.destroyRef);
    }

    public ngOnChanges(): void {
        this.refreshChart();
    }

    private async refreshChart(): Promise<void> {
        this.loading.set(true);

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const [dividendData, currencies] = await Promise.all([
            this.dividendDataService.getDividendDataRange(portfolio.id, this.range()),
            this.currencyService.getCurrenciesMap(),
        ]);

        const currencySymbol = currencies.get(portfolio.currencyId)?.symbol ?? '';
        const formatter = ChartUtils.currencyFormatter(currencySymbol);

        const chartMap = this.mapChart(dividendData);
        const chartOptions = this.initializeChartOptions(formatter);
        chartOptions.xaxis.categories = chartMap.categories;
        chartOptions.series = chartMap.series;
        this.chartOptions = chartOptions;
        this.loading.set(false);
    }

    private initializeChartOptions(formatter?: (value: number) => string): ChartOptions {
        return {
            series: [],
            chart: {
                ...ChartUtils.locale(this.translateService.currentLang() ?? 'en'),
                type: 'bar',
                height: 600,
                stacked: true,
                zoom: {
                    enabled: false,
                },
                toolbar: {
                    show: false,
                },
                animations: {
                    enabled: false,
                },
                nonce: this.nonce ?? undefined,
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                },
            },
            dataLabels: {
                enabled: false,
            },
            xaxis: ChartUtils.xAxis(),
            yaxis: ChartUtils.yAxis(true, formatter),
            legend: {
                show: false,
            },
            theme: ChartUtils.theme(),
            fill: {
                opacity: 1,
            },
            grid: ChartUtils.grid(),
            tooltip: formatter !== undefined ? { y: { formatter } } : {},
            colors: ChartUtils.colors(),
        };
    }

    private mapChart(
        dividendData: DividendDataDateInterval[],
    ): { series: { name: string, data: number[] }[], categories: string[] } {
        const categories: string[] = [];

        const seriesData: Map<number, { name: string, data: number[] }> = new Map();

        for (const portfolioDataDateInterval of dividendData) {
            categories.push(portfolioDataDateInterval.interval);

            for (const dividendDataAsset of portfolioDataDateInterval.dividendDataAssets) {
                const currentSeriesData = seriesData.get(dividendDataAsset.id);

                if (currentSeriesData === undefined) {
                    const initialData: number[] = [];
                    for (let i: number = 0; i < dividendData.length; i += 1) {
                        initialData.push(0);
                    }

                    const seriesDataItem: { name: string, data: number[] } = {
                        name: dividendDataAsset.tickerName,
                        data: initialData,
                    };
                    seriesData.set(dividendDataAsset.id, seriesDataItem);
                }
            }
        }

        let i = 0;
        for (const portfolioDataDateInterval of dividendData) {
            for (const dividendDataAsset of portfolioDataDateInterval.dividendDataAssets) {
                const currentSeriesData = seriesData.get(dividendDataAsset.id);

                if (currentSeriesData === undefined) {
                    continue;
                }

                currentSeriesData.data[i] = parseFloat(dividendDataAsset.dividendYield);
            }

            i += 1;
        }

        const series: { name: string, data: number[] }[] = Array.from(seriesData.values());

        return {
            series,
            categories,
        };
    }
}
