import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit, signal,
} from '@angular/core';
import { MatIcon } from '@angular/material/icon';
import { RouterLink } from '@angular/router';
import { Strategy } from '@app/models';
import { PortfolioService, StrategyService } from '@app/services';
import { PortfolioSelectorComponent } from '@app/shared/components/portfolio-selector/portfolio-selector.component';
import { StrategyDetailComponent } from '@app/strategies/components/detail/strategy-detail.component';
import { StrategyListComponent } from '@app/strategies/components/strategy-list/strategy-list.component';
import { TranslatePipe } from '@ngx-translate/core';

@Component({
    templateUrl: 'strategies.component.html',
    imports: [
        TranslatePipe,
        PortfolioSelectorComponent,
        RouterLink,
        MatIcon,
        StrategyDetailComponent,
        StrategyListComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class StrategiesComponent implements OnInit {
    private readonly strategyService = inject(StrategyService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly changeDetectorRef = inject(ChangeDetectorRef);

    public readonly strategies = signal<Strategy[] | null>(null);
    public readonly selectedStrategyId = signal<number | null>(null);

    public ngOnInit(): void {
        this.refreshStrategies();

        this.strategyService.subscribe(() => {
            this.refreshStrategies();
            this.changeDetectorRef.detectChanges();
        });

        this.portfolioService.subscribe(() => {
            this.refreshStrategies();
            this.changeDetectorRef.detectChanges();
        });
    }

    private async refreshStrategies(): Promise<void> {
        this.strategies.set(null);
        this.selectedStrategyId.set(null);

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const strategies = await this.strategyService.getStrategies(portfolio.id);
        this.strategies.set(strategies);

        const defaultStrategy = strategies.find((s) => s.isDefault);
        if (defaultStrategy !== undefined) {
            this.selectedStrategyId.set(defaultStrategy.id);
        } else if (strategies.length > 0) {
            this.selectedStrategyId.set(strategies[0].id);
        }
    }

    protected selectStrategy(id: number): void {
        this.selectedStrategyId.set(id);
    }

    protected async deleteStrategy(id: number): Promise<void> {
        await this.strategyService.deleteStrategy(id);

        this.strategies.update((strategies) => (strategies !== null
            ? strategies.filter((x) => x.id !== id)
            : null));

        if (this.selectedStrategyId() === id) {
            const remaining = this.strategies();
            if (remaining !== null && remaining.length > 0) {
                this.selectedStrategyId.set(remaining[0].id);
            } else {
                this.selectedStrategyId.set(null);
            }
        }
    }
}
