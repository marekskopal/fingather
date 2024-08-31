import {
    ChangeDetectionStrategy,
    Component, input, InputSignal, OnChanges, OnInit, signal,
} from '@angular/core';
import {
    DividendDataDateInterval
} from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { DividendDataService, PortfolioService } from '@app/services';
import {ChartUtils} from "@app/utils/chart-utils";
import {
    ApexAxisChartSeries, ApexChart, ApexDataLabels, ApexFill, ApexGrid, ApexLegend, ApexPlotOptions,
    ApexTheme, ApexXAxis, ApexYAxis,
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
    colors: string[];
};

@Component({
    templateUrl: 'dividends-data-chart.component.html',
    selector: 'fingather-dividends-data-chart',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DividendsDataChartComponent implements OnInit, OnChanges {
    public readonly range: InputSignal<RangeEnum> = input.required<RangeEnum>();
    public chartOptions: ChartOptions;
    protected readonly loading = signal<boolean>(false);

    public constructor(
        private readonly dividendDataService: DividendDataService,
        private readonly portfolioService: PortfolioService,
    ) {
    }

    public ngOnInit(): void {
        this.refreshChart();

        this.portfolioService.subscribe(() => {
            this.refreshChart();
        });
    }

    public ngOnChanges(): void {
        this.refreshChart();
    }

    private async refreshChart(): Promise<void> {
        this.loading.set(true);

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const dividendData = await this.dividendDataService.getDividendDataRange(portfolio.id, this.range());

        const chartMap = this.mapChart(dividendData);
        const chartOptions = this.initializeChartOptions();
        chartOptions.xaxis.categories = chartMap.categories;
        chartOptions.series = chartMap.series;
        this.chartOptions = chartOptions;
        this.loading.set(false);
    }

    private initializeChartOptions(): ChartOptions {
        return {
            series: [],
            chart: {
                type: 'bar',
                height: 600,
                stacked: true,
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: false,
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: ChartUtils.xAxis(),
            yaxis: ChartUtils.yAxis(),
            legend: {
                show: false
            },
            theme: ChartUtils.theme(),
            fill: {
                opacity: 1
            },
            grid: ChartUtils.grid(),
            colors: ChartUtils.colors(),
        };
    }

    private mapChart(
        dividendData: DividendDataDateInterval[]
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
                        data: initialData
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
