import {
    ChangeDetectionStrategy, Component, computed, inject, input, OnInit, signal
} from '@angular/core';
import {AbstractGroupWithGroupDataEntity} from "@app/models/abstract-group-with-group-data-entity";
import { PortfolioService } from '@app/services';
import {LegendComponent} from "@app/shared/components/legend/legend.component";
import {LegendItem} from "@app/shared/components/legend/types/legend-item";
import {ChartUtils} from "@app/utils/chart-utils";
import {
    ApexChart, ApexFill, ApexLegend,
    ApexNonAxisChartSeries, ApexPlotOptions, ApexStates, ApexStroke, ApexTheme, ApexYAxis, NgApexchartsModule
} from 'ng-apexcharts';

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
    standalone: true,
    imports: [
        NgApexchartsModule,
        LegendComponent
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class GroupChartComponent implements OnInit {
    private readonly portfolioService = inject(PortfolioService);

    public readonly $groupsWithGroupData = input.required<AbstractGroupWithGroupDataEntity[]>({
        alias: 'groupsWithGroupData',
    });

    protected chartOptions: ChartOptions;
    protected readonly $loading = signal<boolean>(false);
    protected readonly $legendItems = computed<LegendItem[]>(() => {
        let $i = 0;
        return this.$groupsWithGroupData().map((groupWithGroupData) => {
            return {
                color: groupWithGroupData.color ?? ChartUtils.getColor($i++),
                name: groupWithGroupData.name,
                value: groupWithGroupData.percentage,
            };
        });
    });

    public constructor() {
        this.initializeChartOptions();
    }

    public async ngOnInit(): Promise<void> {
        this.refreshChart();

        this.portfolioService.subscribe(() => {
            this.refreshChart();
        });
    }

    public async refreshChart(): Promise<void> {
        this.$loading.set(true);

        const chartMap = this.mapChart(this.$groupsWithGroupData());
        this.chartOptions.series = chartMap.series;
        this.chartOptions.labels = chartMap.labels;

        for (let i = 0; i < chartMap.colors.length; i++) {
            this.chartOptions.colors[i] = chartMap.colors[i];
        }

        this.$loading.set(false);
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [],
            chart: {
                height: 240,
                type: 'donut',
                selection: {
                    enabled: false,
                },
                sparkline: {
                    enabled: true,
                }
            },
            labels: [],
            fill: {
                opacity: 1
            },
            stroke: {
                width: 0,
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
                show: false
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '60%'
                    }
                }
            },
            theme: ChartUtils.theme(),
            colors: ChartUtils.colors(),
        };
    }

    private mapChart(
        groupsWithGroupData: AbstractGroupWithGroupDataEntity[]
    ): { series: number[], labels: string[], colors: string[] } {
        const series: number[] = [];
        const labels: string[] = [];
        const colors: string[] = [];

        for (const groupWithGroupData of groupsWithGroupData) {
            series.push(groupWithGroupData.percentage);
            labels.push(groupWithGroupData.name);

            if (groupWithGroupData.color) {
                colors.push(groupWithGroupData.color);
            }
        }

        return {
            series,
            labels,
            colors,
        };
    }
}
