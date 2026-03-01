import {
    ChangeDetectionStrategy,
    Component, CSP_NONCE, effect, inject, input, signal,
} from '@angular/core';
import { FormsModule } from '@angular/forms';
import { DcaPlanProjection } from '@app/models';
import { DcaPlanService } from '@app/services';
import { ChartUtils } from '@app/utils/chart-utils';
import { TranslatePipe } from '@ngx-translate/core';
import {
    ApexAxisChartSeries, ApexChart, ApexDataLabels, ApexFill,
    ApexGrid, ApexLegend, ApexStroke, ApexTheme, ApexXAxis, ApexYAxis, NgApexchartsModule,
} from 'ng-apexcharts';

export type ChartOptions = {
    series: ApexAxisChartSeries;
    chart: ApexChart;
    xaxis: ApexXAxis;
    yaxis: ApexYAxis;
    dataLabels: ApexDataLabels;
    grid: ApexGrid;
    stroke: ApexStroke;
    legend: ApexLegend;
    theme: ApexTheme;
    fill: ApexFill;
    colors: string[];
};

@Component({
    selector: 'fingather-dca-plan-projection-chart',
    templateUrl: 'dca-plan-projection-chart.component.html',
    imports: [
        NgApexchartsModule,
        TranslatePipe,
        FormsModule,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DcaPlanProjectionChartComponent {
    private readonly dcaPlanService = inject(DcaPlanService);
    private readonly nonce = inject(CSP_NONCE);

    public readonly dcaPlanId = input.required<number>();

    protected readonly horizonYears = signal<number>(10);
    protected readonly withCurrentValue = signal<boolean>(true);
    protected readonly loading = signal<boolean>(true);
    protected chartOptions: ChartOptions;

    protected readonly horizonOptions = [5, 10, 20, 30];

    public constructor() {
        this.initializeChartOptions();

        effect(() => {
            const planId = this.dcaPlanId();
            if (planId > 0) {
                this.refreshChart(planId, this.horizonYears(), this.withCurrentValue());
            }
        });
    }

    protected setHorizon(years: number): void {
        this.horizonYears.set(years);
        this.refreshChart(this.dcaPlanId(), years, this.withCurrentValue());
    }

    protected toggleWithCurrentValue(value: boolean): void {
        this.withCurrentValue.set(value);
        this.refreshChart(this.dcaPlanId(), this.horizonYears(), value);
    }

    private async refreshChart(planId: number, horizonYears: number, withCurrentValue: boolean): Promise<void> {
        this.loading.set(true);

        const projection: DcaPlanProjection = await this.dcaPlanService.getProjection(planId, horizonYears, withCurrentValue);

        this.chartOptions.xaxis.categories = projection.dataPoints.map((p) => p.date);
        this.chartOptions.series[0].data = projection.dataPoints.map((p) => parseFloat(p.projectedValue));
        this.chartOptions.series[1].data = projection.dataPoints.map((p) => parseFloat(p.investedCapital));

        this.loading.set(false);
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [
                {
                    name: 'Projected Value',
                    data: [],
                },
                {
                    name: 'Invested Capital',
                    data: [],
                },
            ],
            chart: {
                height: 'auto',
                type: 'area',
                zoom: {
                    enabled: false,
                },
                toolbar: {
                    show: false,
                },
                animations: {
                    enabled: false,
                },
                nonce: this.nonce ?? undefined,
            },
            dataLabels: {
                enabled: false,
            },
            stroke: {
                curve: 'straight',
                width: 3,
            },
            grid: ChartUtils.grid(),
            xaxis: {
                ...ChartUtils.xAxis(true),
                type: 'category',
                categories: [],
            },
            yaxis: ChartUtils.yAxis(true),
            legend: {
                show: true,
            },
            theme: ChartUtils.theme(),
            fill: ChartUtils.gradientFill(),
            colors: ChartUtils.colors(2),
        };
    }
}
