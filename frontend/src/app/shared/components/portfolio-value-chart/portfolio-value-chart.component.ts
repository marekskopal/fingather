import {
    ChangeDetectionStrategy,
    Component, input, InputSignal, OnChanges, OnInit, signal, WritableSignal
} from '@angular/core';
import { PortfolioDataWithBenchmarkData } from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { PortfolioDataService, PortfolioService } from '@app/services';
import {
    ApexAxisChartSeries, ApexChart, ApexDataLabels, ApexFill, ApexGrid, ApexLegend, ApexStroke, ApexTheme,
    ApexTitleSubtitle, ApexXAxis, ApexYAxis
} from 'ng-apexcharts';

export type ChartOptions = {
    series: ApexAxisChartSeries;
    chart: ApexChart;
    xaxis: ApexXAxis;
    yaxis: ApexYAxis;
    dataLabels: ApexDataLabels;
    grid: ApexGrid;
    stroke: ApexStroke;
    title: ApexTitleSubtitle;
    legend: ApexLegend;
    theme: ApexTheme;
    fill: ApexFill;
    colors: string[];
};

@Component({
    templateUrl: 'portfolio-value-chart.component.html',
    selector: 'fingather-portfolio-value-chart',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PortfolioValueChartComponent implements OnInit, OnChanges {
    public readonly $range = input.required<RangeEnum>({
        alias: 'range',
    });
    public readonly $benchmarkAssetId = input<number | null>(null, {
        alias: 'benchmarkAssetId',
    });
    public readonly $height = input<string>('auto', {
        alias: 'height',
    });
    public readonly $showLabels = input<boolean | null>(null, {
        alias: 'showLabels',
    });
    public readonly $title = input<string | null>(null, {
        alias: 'title',
    });
    public readonly $sparkline = input<boolean>(false, {
        alias: 'sparkline',
    });
    protected chartOptions: ChartOptions;
    protected readonly $loading: WritableSignal<boolean> = signal<boolean>(false);

    public constructor(
        private readonly portfolioDataService: PortfolioDataService,
        private readonly portfolioService: PortfolioService,
    ) {
    }

    public ngOnInit(): void {
        this.initializeChartOptions();
        this.initializeBenchmarkChartOptions();

        this.refreshChart();

        this.portfolioService.subscribe(() => {
            this.refreshChart();
        });
    }

    public ngOnChanges(): void {
        this.initializeBenchmarkChartOptions();
        this.refreshChart();
    }

    private async refreshChart(): Promise<void> {
        this.$loading.set(true);

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const portfolioData = await this.portfolioDataService.getPortfolioDataRange(
            portfolio.id,
            this.$range(),
            this.$benchmarkAssetId()
        );

        const chartMap = this.mapChart(portfolioData);
        this.chartOptions.xaxis.categories = chartMap.categories;
        this.chartOptions.series[0].data = chartMap.valueSeries;
        this.chartOptions.series[1].data = chartMap.investedValueSeries;
        if (chartMap.benchmarkSeries.length > 0) {
            this.chartOptions.series[2].data = chartMap.benchmarkSeries;
        }
        this.$loading.set(false);
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [
                {
                    name: 'Value',
                    data: [],
                    zIndex: 3,
                },
                {
                    name: 'Invested Value',
                    data: [],
                    zIndex: 1,
                },
            ],
            chart: {
                height: this.$height(),
                type: 'area',
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: false
                },
                sparkline: {
                    enabled: this.$sparkline(),
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'straight',
                width: 3,
            },
            title: {
                text: this.$title() ?? '',
                floating: true,
                align: 'left',
                margin: 0,
            },
            grid: {
                borderColor: '#454545',
                padding: {
                    top: 0,
                    bottom: 0,
                    left: 0,
                    right: 0,
                }
            },
            xaxis: {
                type: 'datetime',
                labels: {
                    show: this.$showLabels() ?? true,
                },
                categories: [],
            },
            yaxis: {
                labels: {
                    show: this.$showLabels() ?? true,
                }
            },
            legend: {
                show: this.$showLabels() ?? true,
            },
            theme: {
                mode: 'dark',
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    shadeIntensity: 0.9,
                    inverseColors: false,
                    opacityFrom: 0.8,
                    opacityTo: 0,
                    stops: [0, 90, 100]
                },
            },
            colors: ['#9e2af3', '#7597f2'],
        };

        if (this.$benchmarkAssetId() !== null) {
            this.chartOptions.series[2] = {
                name: 'Benchmark',
                data: [],
            };

            this.chartOptions.colors[2] = '#d063ee';
        }
    }

    private initializeBenchmarkChartOptions(): void {
        if (this.$benchmarkAssetId() === null) {
            return;
        }

        this.chartOptions.series[2] = {
            name: 'Benchmark',
            data: [],
            zIndex: 2,
        };

        this.chartOptions.colors[2] = '#d063ee';
    }

    private mapChart(
        portfolioDatas: PortfolioDataWithBenchmarkData[]
    ): {
            valueSeries: number[],
            investedValueSeries: number[],
            benchmarkSeries: number[],
            categories: string[]
        } {
        return {
            valueSeries: portfolioDatas.map((portfolioData) => parseFloat(portfolioData.value)),
            investedValueSeries: portfolioDatas.map(
                (portfolioData) => parseFloat(portfolioData.transactionValue)
            ),
            benchmarkSeries: this.$benchmarkAssetId() !== null
                ? portfolioDatas.map((portfolioData) => parseFloat(portfolioData.benchmarkData?.value ?? '0.0'))
                : [],
            categories: portfolioDatas.map((portfolioData) => portfolioData.date)
        };
    }
}
