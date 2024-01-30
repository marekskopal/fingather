import { Component, Input, OnInit, ViewChild } from '@angular/core';
import {GroupWithGroupData} from '@app/models';
import {GroupWithGroupDataService, PortfolioService} from '@app/services';
import { ApexChart, ApexFill, ApexLegend,
    ApexNonAxisChartSeries, ApexPlotOptions, ApexStroke, ApexTheme, ApexYAxis, ChartComponent } from 'ng-apexcharts';
import { first } from 'rxjs/operators';

export type ChartOptions = {
    series: ApexNonAxisChartSeries;
    chart: ApexChart;
    labels: string[];
    theme: ApexTheme;
    fill: ApexFill,
    yaxis: ApexYAxis,
    stroke: ApexStroke,
    legend: ApexLegend,
    plotOptions: ApexPlotOptions,
};

@Component({
    templateUrl: 'group-chart.component.html',
    selector: 'fingather-dashboard-group-chart',
})
export class GroupChartComponent implements OnInit {
    @ViewChild('chart', { static: false }) public chart: ChartComponent;
    @Input() public assetTickerId: string;
    public chartOptions: ChartOptions;
    public loading: boolean = true;

    public constructor(
        private readonly groupWithGroupDataService: GroupWithGroupDataService,
        private readonly portfolioService: PortfolioService,
    ) {
        this.initializeChartOptions();
    }

    public async ngOnInit(): Promise<void> {
        this.refreshChart();

        this.portfolioService.eventEmitter.subscribe(() => {
            this.refreshChart();
        });
    }

    public async refreshChart(): Promise<void> {
        this.loading = true;

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        this.groupWithGroupDataService.getGroupWithGroupData(portfolio.id)
            .pipe(first())
            .subscribe((groupsWithGroupData: GroupWithGroupData[]) => {
                const chartMap = this.mapChart(groupsWithGroupData);
                this.chartOptions.series = chartMap.series;
                this.chartOptions.labels = chartMap.labels;
                this.loading = false;
            });
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [],
            chart: {
                width: '100%',
                type: 'pie'
            },
            labels: [],
            fill: {
                opacity: 1
            },
            stroke: {
                width: 1,
                colors: undefined
            },
            yaxis: {
                show: false
            },
            legend: {
                position: 'bottom'
            },
            plotOptions: {
                polarArea: {
                    rings: {
                        strokeWidth: 0
                    }
                }
            },
            theme: {
                mode: 'dark',
                monochrome: {
                    enabled: true
                }
            }
        };
    }

    private mapChart(groupsWithGroupData: GroupWithGroupData[]): {series: number[], labels: string[]}
    {
        const series: number[] = [];
        const labels: string[] = [];

        for (const groupWithGroupData of groupsWithGroupData) {
            series.push(groupWithGroupData.percentage);
            labels.push(groupWithGroupData.name);
        }

        return {
            series: series,
            labels: labels,
        };
    }
}
