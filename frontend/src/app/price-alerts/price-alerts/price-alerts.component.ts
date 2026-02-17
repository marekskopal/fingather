import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit, signal,
} from '@angular/core';
import {FormsModule} from "@angular/forms";
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import {PriceAlert} from '@app/models';
import {AlertRecurrenceEnum} from "@app/models/enums/alert-recurrence-enum";
import {PriceAlertService} from "@app/services/price-alert.service";
import {DeleteButtonComponent} from "@app/shared/components/delete-button/delete-button.component";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";
import {TranslatePipe, TranslateService} from "@ngx-translate/core";

@Component({
    templateUrl: 'price-alerts.component.html',
    imports: [
        TranslatePipe,
        MatIcon,
        RouterLink,
        DeleteButtonComponent,
        ScrollShadowDirective,
        FormsModule,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PriceAlertsComponent implements OnInit {
    private readonly priceAlertService = inject(PriceAlertService);
    private readonly translateService = inject(TranslateService);
    private readonly changeDetectorRef = inject(ChangeDetectorRef);

    public readonly priceAlerts = signal<PriceAlert[] | null>(null);

    public ngOnInit(): void {
        this.refreshPriceAlerts();

        this.priceAlertService.subscribe(() => {
            this.refreshPriceAlerts();
            this.changeDetectorRef.detectChanges();
        });
    }

    private async refreshPriceAlerts(): Promise<void> {
        this.priceAlerts.set(null);

        const priceAlerts = await this.priceAlertService.getPriceAlerts();
        this.priceAlerts.set(priceAlerts);
    }

    protected async deletePriceAlert(id: number): Promise<void> {
        const priceAlert = this.priceAlerts()?.find((x) => x.id === id);
        if (priceAlert === undefined) {
            return;
        }

        await this.priceAlertService.deletePriceAlert(id);

        this.priceAlerts.update((priceAlerts) => (priceAlerts !== null
            ? priceAlerts.filter((x) => x.id !== id)
            : null));
    }

    protected async toggleActive(priceAlert: PriceAlert): Promise<void> {
        const updated = await this.priceAlertService.updatePriceAlert(priceAlert.id, {
            ...priceAlert,
            isActive: !priceAlert.isActive,
        });

        this.priceAlerts.update((priceAlerts) => (priceAlerts !== null
            ? priceAlerts.map((x) => x.id === updated.id ? updated : x)
            : null));
    }

    protected getSubjectName(priceAlert: PriceAlert): string {
        if (priceAlert.tickerTicker !== null) {
            return priceAlert.tickerTicker;
        }
        return 'Portfolio';
    }

    protected formatTargetValue(targetValue: string): string {
        return parseFloat(targetValue).toFixed(2);
    }

    protected getRecurrenceLabel(recurrence: AlertRecurrenceEnum): string {
        return recurrence === AlertRecurrenceEnum.OneTime
            ? this.translateService.instant('app.priceAlerts.addEdit.recurrenceOneTime')
            : this.translateService.instant('app.priceAlerts.addEdit.recurrenceRecurring');
    }
}
