import {AsyncPipe, DatePipe, DecimalPipe} from '@angular/common';
import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit, signal,
} from '@angular/core';
import { MatIcon } from '@angular/material/icon';
import { RouterLink } from '@angular/router';
import { DcaPlan } from '@app/models';
import { DcaPlanService, PortfolioService } from '@app/services';
import { DeleteButtonComponent } from '@app/shared/components/delete-button/delete-button.component';
import { PortfolioSelectorComponent } from '@app/shared/components/portfolio-selector/portfolio-selector.component';
import { MoneyPipe } from "@app/shared/pipes/money.pipe";
import { ScrollShadowDirective } from '@marekskopal/ng-scroll-shadow';
import { TranslatePipe } from '@ngx-translate/core';

@Component({
    templateUrl: 'dca-plans.component.html',
    imports: [
        TranslatePipe,
        MatIcon,
        RouterLink,
        DeleteButtonComponent,
        ScrollShadowDirective,
        DatePipe,
        PortfolioSelectorComponent,
        DecimalPipe,
        AsyncPipe,
        MoneyPipe,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DcaPlansComponent implements OnInit {
    private readonly dcaPlanService = inject(DcaPlanService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly changeDetectorRef = inject(ChangeDetectorRef);

    public readonly plans = signal<DcaPlan[] | null>(null);

    public ngOnInit(): void {
        this.refreshPlans();

        this.dcaPlanService.subscribe(() => {
            this.refreshPlans();
            this.changeDetectorRef.detectChanges();
        });

        this.portfolioService.subscribe(() => {
            this.refreshPlans();
            this.changeDetectorRef.detectChanges();
        });
    }

    private async refreshPlans(): Promise<void> {
        this.plans.set(null);

        const currentPortfolio = await this.portfolioService.getCurrentPortfolio();
        this.plans.set(await this.dcaPlanService.getDcaPlans(currentPortfolio.id));
    }

    protected async deletePlan(id: number): Promise<void> {
        await this.dcaPlanService.deleteDcaPlan(id);

        this.plans.update((plans) => (plans !== null
            ? plans.filter((x) => x.id !== id)
            : null));
    }
}
