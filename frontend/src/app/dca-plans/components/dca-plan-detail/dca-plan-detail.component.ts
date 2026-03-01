import {AsyncPipe, DatePipe, DecimalPipe} from '@angular/common';
import {
    ChangeDetectionStrategy, Component, inject, OnInit, signal,
} from '@angular/core';
import { MatIcon } from '@angular/material/icon';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { DcaPlanProjectionChartComponent } from
    '@app/dca-plans/components/dca-plan-projection-chart/dca-plan-projection-chart.component';
import { DcaPlan } from '@app/models';
import { DcaPlanService } from '@app/services';
import {MoneyPipe} from "@app/shared/pipes/money.pipe";
import { TranslatePipe } from '@ngx-translate/core';

@Component({
    templateUrl: 'dca-plan-detail.component.html',
    imports: [
        TranslatePipe,
        MatIcon,
        RouterLink,
        DatePipe,
        DecimalPipe,
        DcaPlanProjectionChartComponent,
        AsyncPipe,
        MoneyPipe,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DcaPlanDetailComponent implements OnInit {
    private readonly dcaPlanService = inject(DcaPlanService);
    private readonly route = inject(ActivatedRoute);

    protected readonly plan = signal<DcaPlan | null>(null);

    public async ngOnInit(): Promise<void> {
        const id = parseInt(this.route.snapshot.params['id'], 10);
        if (!isNaN(id)) {
            this.plan.set(await this.dcaPlanService.getDcaPlan(id));
        }
    }
}
