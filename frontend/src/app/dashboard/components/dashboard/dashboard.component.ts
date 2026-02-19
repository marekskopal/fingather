import {
    ChangeDetectionStrategy, Component,
} from '@angular/core';
import {GroupDataComponent} from "@app/dashboard/components/group-data/group-data.component";
import {GoalsDashboardComponent} from "@app/goals/goals-dashboard/goals-dashboard.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {PortfolioTotalComponent} from "@app/shared/components/portfolio-total/portfolio-total.component";
import {TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: 'dashboard.component.html',
    imports: [
        PortfolioSelectorComponent,
        TranslatePipe,
        PortfolioTotalComponent,
        GoalsDashboardComponent,
        GroupDataComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DashboardComponent {
}
