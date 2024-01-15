import { Component, Input, OnInit, ViewChild } from '@angular/core';
import { first } from 'rxjs/operators';
import {ApexAxisChartSeries, ApexChart, ApexDataLabels, ApexGrid, ApexStroke,
    ApexTitleSubtitle, ApexXAxis, ChartComponent } from 'ng-apexcharts';
import {GroupWithGroupData, PortfolioData, PortfolioDataRangeEnum} from "@app/models";
import {PortfolioDataService} from "@app/services";
import moment from "moment";

export type ChartOptions = {
    series: ApexAxisChartSeries;
    chart: ApexChart;
    xaxis: ApexXAxis;
    dataLabels: ApexDataLabels;
    grid: ApexGrid;
    stroke: ApexStroke;
    title: ApexTitleSubtitle;
};

@Component({
    templateUrl: 'portfolio-value-chart.component.html',
    selector: 'fingather-history-portfolio-value-chart',
})
export class PortfolioValueChartComponent implements OnInit {
    @ViewChild("chart", { static: false }) public chart: ChartComponent;
    @Input() public assetTickerId: string;
    public chartOptions: ChartOptions;
    public loading: boolean = true;

    public constructor(
        private portfolioDataService: PortfolioDataService,
    ) {
        this.initializeChartOptions();
    }

    public ngOnInit(): void {
        this.portfolioDataService.getPortfolioDataRange(PortfolioDataRangeEnum.SevenDays)
            .pipe(first())
            .subscribe((portfolioData: PortfolioData[]) => {
                const chartMap = this.mapChart(portfolioData);
                this.chartOptions.xaxis.categories = chartMap.categories;
                this.chartOptions.series[0].data = chartMap.series;
                this.loading = false;
            });
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [{
                name: 'Value: ',
                data: [],
            }],
            chart: {
                height: "350",
                type: "line",
                zoom: {
                    enabled: false
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
            }
        };
    }

    private mapChart(portfolioDatas: PortfolioData[]): {series: number[], categories: string[]}
    {
        const series: number[] = [];
        const categories: string[] = [];

        for (const portfolioData of portfolioDatas) {
            series.push(portfolioData.value);
            categories.push(portfolioData.date);
        }

        return {
            series: series,
            categories: categories,
        };
    }
}
