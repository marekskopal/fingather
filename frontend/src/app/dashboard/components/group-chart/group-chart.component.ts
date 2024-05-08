import { Component, OnInit } from '@angular/core';
import { GroupWithGroupData } from '@app/models';
import { GroupWithGroupDataService, PortfolioService } from '@app/services';
import {
    ApexChart, ApexFill, ApexLegend,
    ApexNonAxisChartSeries, ApexPlotOptions, ApexStates, ApexStroke, ApexTheme, ApexYAxis
} from 'ng-apexcharts';
import { first } from 'rxjs/operators';

export type ChartOptions = {
    series: ApexNonAxisChartSeries;
    chart: ApexChart;
    labels: string[];
    theme: ApexTheme;
    fill: ApexFill,
    yaxis: ApexYAxis,
    stroke: ApexStroke,
    states: ApexStates,
    legend: ApexLegend,
    plotOptions: ApexPlotOptions,
    colors: string[],
};

@Component({
    templateUrl: 'group-chart.component.html',
    selector: 'fingather-dashboard-group-chart',
})
export class GroupChartComponent implements OnInit {
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
                this.chartOptions.colors = chartMap.colors;
                this.loading = false;
            });
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [],
            chart: {
                width: '100%',
                type: 'donut',
                selection: {
                    enabled: false,
                },
            },
            labels: [],
            fill: {
                opacity: 1
            },
            stroke: {
                width: 4,
                colors: ['#1b2627']
            },
            states: {
                active: {
                    filter: {
                        type: 'none'
                    }
                }
            },
            yaxis: {
                show: false
            },
            legend: {
                position: 'bottom'
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '50%'
                    }
                }
            },
            theme: {
                mode: 'dark',
            },
            colors: [],
        };
    }

    private mapChart(
        groupsWithGroupData: GroupWithGroupData[]
    ): { series: number[], labels: string[], colors: string[] } {
        const series: number[] = [];
        const labels: string[] = [];
        const colors: string[] = [];

        for (const groupWithGroupData of groupsWithGroupData) {
            series.push(groupWithGroupData.percentage);
            labels.push(groupWithGroupData.name);
            colors.push(groupWithGroupData.color);
        }

        return {
            series,
            labels,
            colors,
        };
    }
}
