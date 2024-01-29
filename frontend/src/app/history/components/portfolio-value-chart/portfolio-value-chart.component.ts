import {Component, Input, OnChanges, OnInit, ViewChild} from '@angular/core';
import { first } from 'rxjs/operators';
import {
    ApexAxisChartSeries, ApexChart, ApexDataLabels, ApexGrid, ApexStroke, ApexTheme,
    ApexTitleSubtitle, ApexXAxis, ChartComponent
} from 'ng-apexcharts';
import {Asset, PortfolioDataRangeEnum, PortfolioDataWithBenchmarkData} from "@app/models";
import {PortfolioDataService, PortfolioService} from "@app/services";

export type ChartOptions = {
    series: ApexAxisChartSeries;
    chart: ApexChart;
    xaxis: ApexXAxis;
    dataLabels: ApexDataLabels;
    grid: ApexGrid;
    stroke: ApexStroke;
    title: ApexTitleSubtitle;
    theme: ApexTheme;
};

@Component({
    templateUrl: 'portfolio-value-chart.component.html',
    selector: 'fingather-history-portfolio-value-chart',
})
export class PortfolioValueChartComponent implements OnInit, OnChanges {
    @ViewChild("chart", { static: false }) public chart: ChartComponent;
    @Input() public range: PortfolioDataRangeEnum;
    @Input() public benchmarkAssetId: number|null;
    public chartOptions: ChartOptions;
    public loading: boolean = true;

    public constructor(
        private readonly portfolioDataService: PortfolioDataService,
        private readonly portfolioService: PortfolioService,
    ) {
        this.initializeChartOptions();
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

        this.portfolioDataService.getPortfolioDataRange(portfolio.id, this.range, this.benchmarkAssetId)
            .pipe(first())
            .subscribe((portfolioData: PortfolioDataWithBenchmarkData[]) => {
                const chartMap = this.mapChart(portfolioData);
                this.chartOptions.xaxis.categories = chartMap.categories;
                this.chartOptions.series[0].data = chartMap.series;
                if (chartMap.benchmarkSeries.length > 0) {
                    this.chartOptions.series[1].data = chartMap.benchmarkSeries;
                }
                this.loading = false;
            });
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [
                {
                    name: 'Value',
                    data: [],
                },
                {
                    name: 'Benchmark',
                    data: [],
                },
            ],
            chart: {
                height: "500",
                type: "line",
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: "smooth"
            },
            title: {
                text: "Portfolio Value",
                align: "left"
            },
            grid: {
                row: {
                    colors: ['#2b3035', 'transparent'],
                    opacity: 0.5
                }
            },
            xaxis: {
                type: 'datetime',
                categories: []
            },
            theme: {
                mode: 'dark',
                monochrome: {
                    enabled: true
                }
            }
        };
    }

    private mapChart(portfolioDatas: PortfolioDataWithBenchmarkData[]): {series: number[], benchmarkSeries: number[], categories: string[]}
    {
        const series: number[] = [];
        const benchmarkSeries: number[] = [];
        const categories: string[] = [];

        for (const portfolioData of portfolioDatas) {
            series.push(portfolioData.value);
            categories.push(portfolioData.date);

            if (portfolioData.benchmarkData !== null) {
                benchmarkSeries.push(portfolioData.benchmarkData.value);
            }
        }

        return {
            series: series,
            benchmarkSeries: benchmarkSeries,
            categories: categories,
        };
    }
}
