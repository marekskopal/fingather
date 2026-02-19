import { DatePipe } from '@angular/common';
import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit, signal,
} from '@angular/core';
import { MatIcon } from '@angular/material/icon';
import { RouterLink } from '@angular/router';
import { GoalProgressBarComponent } from '@app/goals/goal-progress-bar/goal-progress-bar.component';
import { Goal } from '@app/models';
import { GoalTypeEnum } from '@app/models/enums/goal-type-enum';
import { PortfolioService } from '@app/services';
import { GoalService } from '@app/services/goal.service';
import { TranslatePipe, TranslateService } from '@ngx-translate/core';

@Component({
    selector: 'fingather-goals-dashboard',
    templateUrl: 'goals-dashboard.component.html',
    imports: [
        TranslatePipe,
        RouterLink,
        MatIcon,
        DatePipe,
        GoalProgressBarComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class GoalsDashboardComponent implements OnInit {
    private readonly goalService = inject(GoalService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly translateService = inject(TranslateService);
    private readonly changeDetectorRef = inject(ChangeDetectorRef);

    public readonly goals = signal<Goal[] | null>(null);

    public ngOnInit(): void {
        this.refreshGoals();

        this.portfolioService.subscribe(() => {
            this.refreshGoals();
            this.changeDetectorRef.detectChanges();
        });
    }

    private async refreshGoals(): Promise<void> {
        this.goals.set(null);

        const currentPortfolio = await this.portfolioService.getCurrentPortfolio();
        const goals = await this.goalService.getGoals(currentPortfolio.id);
        this.goals.set(goals.filter((g) => g.isActive));
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
}
