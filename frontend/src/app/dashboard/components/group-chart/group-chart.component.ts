import { Component, Input, OnInit, ViewChild } from '@angular/core';
import { first } from 'rxjs/operators';
import { ApexChart, ApexFill, ApexLegend, ApexNonAxisChartSeries, ApexPlotOptions, ApexStroke, ApexTheme, ApexYAxis, ChartComponent } from 'ng-apexcharts';
import {GroupWithGroupData} from "@app/models";
import {GroupWithGroupDataService} from "@app/services";

export type ChartOptions = {
    series: ApexNonAxisChartSeries;
    chart: ApexChart;
    labels: string[];
    theme: ApexTheme;
    fill: ApexFill,
    yaxis: ApexYAxis,
    stroke: ApexStroke,
    legend: ApexLegend,
    plotOptions: ApexPlotOptions
};

@Component({
    templateUrl: 'group-chart.component.html',
    selector: 'fingather-dashboard-group-chart',
})
export class GroupChartComponent implements OnInit {
    @ViewChild("chart", { static: false }) public chart: ChartComponent;
    @Input() public assetTickerId: string;
    public chartOptions: ChartOptions;

    public constructor(
        private groupWithGroupDataService: GroupWithGroupDataService,
    ) {
        this.initializeChartOptions();
    }

    public ngOnInit(): void {
        this.groupWithGroupDataService.getGroupWithGroupData()
            .pipe(first())
            .subscribe((groupsWithGroupData: GroupWithGroupData[]) => {
                const chartMap = this.mapChart(groupsWithGroupData);
                this.chartOptions.series = chartMap.series;
                this.chartOptions.labels = chartMap.labels;
            });
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [],
            chart: {
                width: "100%",
                type: "pie"
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
