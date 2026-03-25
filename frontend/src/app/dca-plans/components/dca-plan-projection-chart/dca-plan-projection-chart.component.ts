import {
    ChangeDetectionStrategy,
    Component, CSP_NONCE, effect, inject, input, signal,
} from '@angular/core';
import { FormsModule } from '@angular/forms';
import { DcaPlanProjection, Goal } from '@app/models';
import { GoalTypeEnum } from '@app/models/enums/goal-type-enum';
import { DcaPlanService } from '@app/services';
import { ChartUtils } from '@app/utils/chart-utils';
import { TranslatePipe, TranslateService } from '@ngx-translate/core';
import {
    ApexAnnotations, ApexAxisChartSeries, ApexChart, ApexDataLabels, ApexFill,
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
    annotations: ApexAnnotations;
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
    private readonly translateService = inject(TranslateService);

    public readonly dcaPlanId = input.required<number>();
    public readonly goals = input<Goal[]>([]);

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

        effect(() => {
            this.chartOptions.annotations = this.buildGoalAnnotations(this.goals());
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

    private buildGoalAnnotations(goals: Goal[]): ApexAnnotations {
        const reachableGoals = goals.filter((g) => g.type !== GoalTypeEnum.ReturnPercentage);
        if (reachableGoals.length === 0) {
            return {};
        }

        return {
            yaxis: reachableGoals.map((goal) => ({
                y: parseFloat(goal.targetValue),
                borderColor: goal.isReachable === true ? '#28a745' : '#dc3545',
                strokeDashArray: 4,
                label: {
                    text: `${this.translateService.instant('app.goals.goals.reachability')}: ${parseFloat(goal.targetValue).toLocaleString()}`,
                    style: {
                        color: '#fff',
                        background: goal.isReachable === true ? '#28a745' : '#dc3545',
                    },
                },
            })),
        };
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [
                {
                    name: this.translateService.instant('app.dcaPlans.projection.seriesProjectedValue'),
                    data: [],
                },
                {
                    name: this.translateService.instant('app.dcaPlans.projection.seriesInvestedCapital'),
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
            annotations: {},
        };
    }
}
