import {
    ChangeDetectionStrategy, Component, computed, CSP_NONCE, inject, input,
} from '@angular/core';
import { ChartUtils } from '@app/utils/chart-utils';
import {
    ApexChart, ApexFill, ApexLegend,
    ApexNonAxisChartSeries, ApexPlotOptions, ApexStates, ApexStroke, ApexTheme, ApexYAxis, NgApexchartsModule,
} from 'ng-apexcharts';

export interface StrategyChartItem {
    name: string;
    percentage: number;
    color?: string | null;
}

export type ChartOptions = {
    series: ApexNonAxisChartSeries;
    chart: ApexChart;
    labels: string[];
    theme: ApexTheme;
    fill: ApexFill;
    yaxis: ApexYAxis;
    stroke: ApexStroke;
    states: ApexStates;
    legend: ApexLegend;
    plotOptions: ApexPlotOptions;
    colors: string[];
};

@Component({
    selector: 'fingather-strategy-chart',
    templateUrl: 'strategy-chart.component.html',
    imports: [
        NgApexchartsModule,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class StrategyChartComponent {
    private readonly nonce = inject(CSP_NONCE);

    public readonly items = input.required<StrategyChartItem[]>();
    public readonly title = input.required<string>();

    protected readonly chartOptions = computed<ChartOptions>(() => {
        const items = this.items();
        const series: number[] = [];
        const labels: string[] = [];
        const colors: string[] = [];

        let i = 0;
        for (const item of items) {
            series.push(item.percentage);
            labels.push(item.name);
            colors.push(item.color ?? ChartUtils.getColor(i));
            i++;
        }

        return {
            series,
            chart: {
                height: 240,
                width: 240,
                type: 'donut',
                selection: {
                    enabled: false,
                },
                sparkline: {
                    enabled: true,
                },
                nonce: this.nonce ?? undefined,
            },
            labels,
            fill: {
                opacity: 1,
            },
            stroke: {
                width: 0,
            },
            states: {
                active: {
                    filter: {
                        type: 'none',
                    },
                },
            },
            yaxis: {
                show: false,
            },
            legend: {
                show: false,
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '60%',
                    },
                },
            },
            theme: ChartUtils.theme(),
            colors,
        };
    });
}
