import { DatePipe } from '@angular/common';
import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit, signal,
} from '@angular/core';
import { FormsModule } from '@angular/forms';
import { MatIcon } from '@angular/material/icon';
import { RouterLink } from '@angular/router';
import { GoalProgressBarComponent } from '@app/goals/goal-progress-bar/goal-progress-bar.component';
import { Goal } from '@app/models';
import { GoalTypeEnum } from '@app/models/enums/goal-type-enum';
import { PortfolioService } from '@app/services';
import { GoalService } from '@app/services/goal.service';
import { DeleteButtonComponent } from '@app/shared/components/delete-button/delete-button.component';
import { PortfolioSelectorComponent } from '@app/shared/components/portfolio-selector/portfolio-selector.component';
import { ScrollShadowDirective } from '@marekskopal/ng-scroll-shadow';
import { TranslatePipe, TranslateService } from '@ngx-translate/core';

@Component({
    templateUrl: 'goals.component.html',
    imports: [
        TranslatePipe,
        MatIcon,
        RouterLink,
        DeleteButtonComponent,
        ScrollShadowDirective,
        FormsModule,
        DatePipe,
        GoalProgressBarComponent,
        PortfolioSelectorComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class GoalsComponent implements OnInit {
    private readonly goalService = inject(GoalService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly translateService = inject(TranslateService);
    private readonly changeDetectorRef = inject(ChangeDetectorRef);

    public readonly goals = signal<Goal[] | null>(null);

    public ngOnInit(): void {
        this.refreshGoals();

        this.goalService.subscribe(() => {
            this.refreshGoals();
            this.changeDetectorRef.detectChanges();
        });

        this.portfolioService.subscribe(() => {
            this.refreshGoals();
            this.changeDetectorRef.detectChanges();
        });
    }

    private async refreshGoals(): Promise<void> {
        this.goals.set(null);

        const currentPortfolio = await this.portfolioService.getCurrentPortfolio();
        this.goals.set(await this.goalService.getGoals(currentPortfolio.id));
    }

    protected async deleteGoal(id: number): Promise<void> {
        const goal = this.goals()?.find((x) => x.id === id);
        if (goal === undefined) {
            return;
        }

        await this.goalService.deleteGoal(id);

        this.goals.update((goals) => (goals !== null
            ? goals.filter((x) => x.id !== id)
            : null));
    }

    protected async toggleActive(goal: Goal): Promise<void> {
        const updated = await this.goalService.updateGoal(goal.id, {
            ...goal,
            isActive: !goal.isActive,
        });

        this.goals.update((goals) => (goals !== null
            ? goals.map((x) => x.id === updated.id ? updated : x)
            : null));
    }

    protected getTypeLabel(type: GoalTypeEnum): string {
        return this.translateService.instant('app.goals.type.' + this.getTypeKey(type));
    }

    private getTypeKey(type: GoalTypeEnum): string {
        switch (type) {
            case GoalTypeEnum.PortfolioValue:
                return 'portfolioValue';
            case GoalTypeEnum.ReturnPercentage:
                return 'returnPercentage';
            case GoalTypeEnum.InvestedAmount:
                return 'investedAmount';
        }
    }

    protected formatValue(value: string): string {
        return parseFloat(value).toFixed(2);
    }
}
