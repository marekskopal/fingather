import { DecimalPipe } from '@angular/common';
import { ChangeDetectionStrategy, Component, input } from '@angular/core';
import { MatIcon } from '@angular/material/icon';
import { PortfolioRiskData } from '@app/models';
import { HelpComponent } from '@app/shared/components/help/help.component';
import { TranslatePipe } from '@ngx-translate/core';

@Component({
    selector: 'fingather-risk-metrics',
    templateUrl: 'risk-metrics.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        TranslatePipe,
        DecimalPipe,
        HelpComponent,
        MatIcon,
    ],
})
export class RiskMetricsComponent {
    public readonly riskData = input.required<PortfolioRiskData>();
    public readonly hasBenchmark = input<boolean>(false);
}
