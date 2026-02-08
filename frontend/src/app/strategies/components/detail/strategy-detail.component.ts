import { DecimalPipe, NgClass } from '@angular/common';
import {
    ChangeDetectionStrategy, Component, effect, inject, input, signal,
} from '@angular/core';
import { StrategyWithComparison } from '@app/models';
import { StrategyComparisonService } from '@app/services';
import { LegendComponent } from '@app/shared/components/legend/legend.component';
import { LegendItem } from '@app/shared/components/legend/types/legend-item';
import {
    StrategyChartComponent, StrategyChartItem,
} from '@app/strategies/components/detail/components/strategy-chart/strategy-chart.component';
import { ChartUtils } from '@app/utils/chart-utils';
import { TranslatePipe } from '@ngx-translate/core';

@Component({
    selector: 'fingather-strategy-detail',
    templateUrl: 'strategy-detail.component.html',
    imports: [
        TranslatePipe,
        StrategyChartComponent,
        LegendComponent,
        DecimalPipe,
        NgClass,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class StrategyDetailComponent {
    private readonly strategyComparisonService = inject(StrategyComparisonService);

    public readonly strategyId = input.required<number>();

    protected readonly loading = signal<boolean>(true);
    protected readonly strategyWithComparison = signal<StrategyWithComparison | null>(null);

    protected readonly targetChartItems = signal<StrategyChartItem[]>([]);
    protected readonly actualChartItems = signal<StrategyChartItem[]>([]);
    protected readonly legendItems = signal<LegendItem[]>([]);

    public constructor() {
        effect(() => {
            this.loadComparison(this.strategyId());
        });
    }

    private async loadComparison(strategyId: number): Promise<void> {
        this.loading.set(true);

        const comparison = await this.strategyComparisonService.getStrategyWithComparison(strategyId);
        this.strategyWithComparison.set(comparison);

        this.targetChartItems.set(comparison.comparisonItems.map((item) => ({
            name: item.name,
            percentage: item.targetPercentage,
            color: item.color,
        })));

        this.actualChartItems.set(comparison.comparisonItems.map((item) => ({
            name: item.name,
            percentage: item.actualPercentage,
            color: item.color,
        })));

        let i = 0;
        this.legendItems.set(comparison.comparisonItems.map((item) => ({
            color: item.color ?? ChartUtils.getColor(i++),
            name: item.name,
        })));

        this.loading.set(false);
    }
}
