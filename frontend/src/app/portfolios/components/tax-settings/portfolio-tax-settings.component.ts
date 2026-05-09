import {
    ChangeDetectionStrategy,
    Component,
    inject,
    OnInit,
} from '@angular/core';
import { ReactiveFormsModule, Validators } from '@angular/forms';
import { MatIcon } from '@angular/material/icon';
import { Router, RouterLink } from '@angular/router';
import {
    CostBasisMethod,
    PortfolioTaxSettings,
    PortfolioTaxSettingsUpdate,
    TaxJurisdiction,
} from '@app/models/portfolio-tax-settings';
import { PortfolioTaxSettingsService } from '@app/services/portfolio-tax-settings.service';
import { BaseAddEditForm } from '@app/shared/components/form/base-add-edit-form';
import { InputValidatorComponent } from '@app/shared/components/input-validator/input-validator.component';
import { PortfolioSelectorComponent } from '@app/shared/components/portfolio-selector/portfolio-selector.component';
import { SaveButtonComponent } from '@app/shared/components/save-button/save-button.component';
import { SelectComponent } from '@app/shared/components/select/select.component';
import { SelectItem } from '@app/shared/types/select-item';
import { TranslatePipe, TranslateService } from '@ngx-translate/core';

const ALL_COST_BASIS_METHODS: CostBasisMethod[] = ['Fifo', 'Lifo', 'AverageCost'];

@Component({
    templateUrl: 'portfolio-tax-settings.component.html',
    imports: [
        PortfolioSelectorComponent,
        TranslatePipe,
        ReactiveFormsModule,
        InputValidatorComponent,
        SelectComponent,
        RouterLink,
        SaveButtonComponent,
        MatIcon,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PortfolioTaxSettingsComponent extends BaseAddEditForm implements OnInit {
    private readonly taxSettingsService = inject(PortfolioTaxSettingsService);
    private readonly router = inject(Router);
    private readonly translateService = inject(TranslateService);

    protected jurisdictionItems: SelectItem<string, string>[] = [];
    protected costBasisMethodItems: SelectItem<string, string>[] = [];
    protected longTermHoldingDays: number | null = null;
    protected defaultEstimatedTaxRate: string | null = null;

    public async ngOnInit(): Promise<void> {
        this.loading.set(true);
        this.initializeIdFromRoute();

        const id = this.id();
        if (id === null) {
            this.loading.set(false);
            return;
        }

        const settings = await this.taxSettingsService.getTaxSettings(id);

        this.jurisdictionItems = (['CzechRepublic', 'Generic'] as TaxJurisdiction[]).map((value) => ({
            key: value,
            label: this.translateService.instant(`app.portfolios.taxSettings.jurisdiction.${value}`),
        }));

        this.refreshCostBasisMethodItems(settings.allowedCostBasisMethods);
        this.longTermHoldingDays = settings.longTermHoldingDays;
        this.defaultEstimatedTaxRate = settings.defaultEstimatedTaxRate;

        this.form = this.formBuilder.group({
            taxJurisdiction: [settings.taxJurisdiction, Validators.required],
            costBasisMethod: [settings.costBasisMethod, Validators.required],
            estimatedTaxRate: [settings.estimatedTaxRate ?? ''],
        });

        this.form.controls['taxJurisdiction'].valueChanges.subscribe((jurisdiction: TaxJurisdiction) => {
            const allowed = jurisdiction === 'CzechRepublic'
                ? (['Fifo', 'AverageCost'] as CostBasisMethod[])
                : ALL_COST_BASIS_METHODS;
            this.refreshCostBasisMethodItems(allowed);
            const currentMethod = this.form.value.costBasisMethod as CostBasisMethod;
            if (!allowed.includes(currentMethod)) {
                this.form.patchValue({ costBasisMethod: allowed[0] });
            }
            this.longTermHoldingDays = jurisdiction === 'CzechRepublic' ? 1095 : null;
            this.defaultEstimatedTaxRate = jurisdiction === 'CzechRepublic' ? '0.15' : null;
        });

        this.loading.set(false);
    }

    public onSubmit(): void {
        this.submitted.set(true);
        this.alertService.clear();

        if (this.form.invalid) {
            return;
        }

        const id = this.id();
        if (id === null) {
            return;
        }

        this.saving.set(true);

        const raw = this.form.value as { taxJurisdiction: TaxJurisdiction; costBasisMethod: CostBasisMethod; estimatedTaxRate: string };
        const update: PortfolioTaxSettingsUpdate = {
            taxJurisdiction: raw.taxJurisdiction,
            costBasisMethod: raw.costBasisMethod,
            estimatedTaxRate: raw.estimatedTaxRate.trim() === '' ? null : raw.estimatedTaxRate.trim(),
        };

        this.taxSettingsService.updateTaxSettings(id, update)
            .then((updated: PortfolioTaxSettings) => {
                this.alertService.success(
                    this.translateService.instant('app.portfolios.taxSettings.updatedSuccessfully'),
                    { keepAfterRouteChange: true },
                );
                this.refreshCostBasisMethodItems(updated.allowedCostBasisMethods);
                this.router.navigate(['../../'], { relativeTo: this.route });
            })
            .catch((error: unknown) => {
                if (error instanceof Error) {
                    this.alertService.error(error.message);
                }
            })
            .finally(() => {
                this.saving.set(false);
            });
    }

    private refreshCostBasisMethodItems(allowed: CostBasisMethod[]): void {
        const allowedSet = new Set(allowed);
        this.costBasisMethodItems = ALL_COST_BASIS_METHODS
            .filter((method) => allowedSet.has(method))
            .map((method) => ({
                key: method,
                label: this.translateService.instant(`app.portfolios.taxSettings.costBasisMethod.${method}`),
            }));
    }
}
