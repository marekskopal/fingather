import {
    ChangeDetectionStrategy,
    Component, inject, OnInit, signal,
} from '@angular/core';
import {ReactiveFormsModule, Validators} from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import {Router, RouterLink} from "@angular/router";
import {Portfolio} from '@app/models';
import {AlertConditionEnum} from "@app/models/enums/alert-condition-enum";
import {AlertRecurrenceEnum} from "@app/models/enums/alert-recurrence-enum";
import {PriceAlertTypeEnum} from "@app/models/enums/price-alert-type-enum";
import {PortfolioService, PriceAlertService} from '@app/services';
import {TranslateService} from "@ngx-translate/core";
import {BaseAddEditForm} from "@app/shared/components/form/base-add-edit-form";
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import {SelectComponent} from "@app/shared/components/select/select.component";
import {TickerSearchSelectorComponent} from "@app/shared/components/ticker-search-selector/ticker-search-selector.component";
import {SelectItem} from "@app/shared/types/select-item";
import {TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: 'add-edit-price-alert.component.html',
    imports: [
        ReactiveFormsModule,
        RouterLink,
        MatIcon,
        InputValidatorComponent,
        SaveButtonComponent,
        TranslatePipe,
        SelectComponent,
        TickerSearchSelectorComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AddEditPriceAlertComponent extends BaseAddEditForm implements OnInit {
    private readonly priceAlertService = inject(PriceAlertService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly translateService = inject(TranslateService);
    private readonly router = inject(Router);

    protected readonly PriceAlertTypeEnum = PriceAlertTypeEnum;
    protected readonly AlertRecurrenceEnum = AlertRecurrenceEnum;

    protected readonly portfolios = signal<SelectItem<number, string>[]>([]);

    protected types: SelectItem<PriceAlertTypeEnum, string>[] = [];
    protected conditions: SelectItem<AlertConditionEnum, string>[] = [];
    protected recurrences: SelectItem<AlertRecurrenceEnum, string>[] = [];

    public async ngOnInit(): Promise<void> {
        this.loading.set(true);

        this.initializeIdFromRoute();

        this.types = [
            {key: PriceAlertTypeEnum.Price, label: PriceAlertTypeEnum.Price},
            {key: PriceAlertTypeEnum.Portfolio, label: PriceAlertTypeEnum.Portfolio},
        ];

        this.conditions = [
            {key: AlertConditionEnum.Above, label: AlertConditionEnum.Above},
            {key: AlertConditionEnum.Below, label: AlertConditionEnum.Below},
        ];

        this.recurrences = [
            {key: AlertRecurrenceEnum.OneTime, label: this.translateService.instant('app.priceAlerts.addEdit.recurrenceOneTime')},
            {key: AlertRecurrenceEnum.Recurring, label: this.translateService.instant('app.priceAlerts.addEdit.recurrenceRecurring')},
        ];

        this.form = this.formBuilder.group({
            type: [PriceAlertTypeEnum.Price, Validators.required],
            condition: [AlertConditionEnum.Above, Validators.required],
            targetValue: ['', Validators.required],
            recurrence: [AlertRecurrenceEnum.OneTime, Validators.required],
            cooldownHours: [24],
            portfolioId: [null],
            tickerId: [null],
        });

        await this.loadPortfolios();

        const id = this.id();
        if (id !== null) {
            const priceAlert = await this.priceAlertService.getPriceAlert(id);
            this.form.patchValue(priceAlert);
        }

        this.loading.set(false);
    }

    private async loadPortfolios(): Promise<void> {
        const portfolios = await this.portfolioService.getPortfolios();
        this.portfolios.set(portfolios.map((p: Portfolio) => ({key: p.id, label: p.name})));
    }

    public onSubmit(): void {
        this.submitted.set(true);

        this.alertService.clear();

        if (this.form.invalid) {
            return;
        }

        this.saving.set(true);
        try {
            if (this.id() === null) {
                this.createPriceAlert();
            } else {
                this.updatePriceAlert();
            }
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.saving.set(false);
        }
    }

    private async createPriceAlert(): Promise<void> {
        await this.priceAlertService.createPriceAlert(this.form.value);

        this.alertService.success('Price alert added successfully', { keepAfterRouteChange: true });
        this.priceAlertService.notify();
        this.router.navigate([this.routerBackLink()], { relativeTo: this.route });
    }

    private async updatePriceAlert(): Promise<void> {
        const id = this.id();
        if (id === null) {
            return;
        }

        await this.priceAlertService.updatePriceAlert(id, this.form.value);

        this.alertService.success('Update successful', { keepAfterRouteChange: true });
        this.priceAlertService.notify();
        this.router.navigate([this.routerBackLink()], { relativeTo: this.route });
    }
}
