import {
    ChangeDetectionStrategy, Component
} from '@angular/core';
import {GroupDataComponent} from "@app/dashboard/components/group-data/group-data.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {PortfolioTotalComponent} from "@app/shared/components/portfolio-total/portfolio-total.component";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'dashboard.component.html',
    standalone: true,
    imports: [
        PortfolioSelectorComponent,
        TranslateModule,
        PortfolioTotalComponent,
        GroupDataComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DashboardComponent {
}
