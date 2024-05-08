import {
    Component, input, InputSignal, OnChanges, OnInit,
} from '@angular/core';
import {
    DividendDataDateInterval
} from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { DividendDataService, PortfolioService } from '@app/services';
import {
    ApexAxisChartSeries, ApexChart, ApexDataLabels, ApexFill, ApexLegend, ApexPlotOptions,
    ApexTheme, ApexXAxis,
} from 'ng-apexcharts';
import { first } from 'rxjs/operators';

export type ChartOptions = {
    series: ApexAxisChartSeries;
    chart: ApexChart;
    plotOptions: ApexPlotOptions;
    dataLabels: ApexDataLabels;
    xaxis: ApexXAxis;
    legend: ApexLegend;
    theme: ApexTheme;
    fill: ApexFill;
};

@Component({
    templateUrl: 'dividends-data-chart.component.html',
    selector: 'fingather-dividends-data-chart',
})
export class DividendsDataChartComponent implements OnInit, OnChanges {
    public range: InputSignal<RangeEnum> = input.required<RangeEnum>();
    public chartOptions: ChartOptions;
    public loading: boolean = true;

    public constructor(
        private readonly dividendDataService: DividendDataService,
        private readonly portfolioService: PortfolioService,
    ) {
    }

    public ngOnInit(): void {
        this.refreshChart();

        this.portfolioService.eventEmitter.subscribe(() => {
            this.refreshChart();
        });
    }

    public ngOnChanges(): void {
        this.refreshChart();
    }

    private async refreshChart(): Promise<void> {
        this.loading = true;

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        this.dividendDataService.getDividendDataRange(portfolio.id, this.range())
            .pipe(first())
            .subscribe((dividendData: DividendDataDateInterval[]) => {
                const chartMap = this.mapChart(dividendData);
                const chartOptions = this.initializeChartOptions();
                chartOptions.xaxis.categories = chartMap.categories;
                chartOptions.series = chartMap.series;
                this.chartOptions = chartOptions;
                this.loading = false;
            });
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
            xaxis: {
                type: 'datetime',
                categories: []
            },
            legend: {
                show: false
            },
            theme: {
                mode: 'dark',
            },
            fill: {
                opacity: 1
            }
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

                currentSeriesData.data[i] = parseFloat(dividendDataAsset.dividendGain);
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
