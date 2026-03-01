import {
    ChangeDetectionStrategy,
    Component, inject, OnInit, signal,
} from '@angular/core';
import { ReactiveFormsModule, Validators } from '@angular/forms';
import { MatIcon } from '@angular/material/icon';
import { Router, RouterLink } from '@angular/router';
import { Asset, Currency, Group, Strategy } from '@app/models';
import { DcaPlanTargetTypeEnum } from '@app/models/enums/dca-plan-target-type-enum';
import {
    AssetService, CurrencyService, DcaPlanService, GroupService, PortfolioService, StrategyService,
} from '@app/services';
import { DateInputComponent } from '@app/shared/components/date-input/date-input.component';
import { BaseAddEditForm } from '@app/shared/components/form/base-add-edit-form';
import { InputValidatorComponent } from '@app/shared/components/input-validator/input-validator.component';
import { SaveButtonComponent } from '@app/shared/components/save-button/save-button.component';
import { SelectComponent } from '@app/shared/components/select/select.component';
import { SelectItem } from '@app/shared/types/select-item';
import { TranslatePipe, TranslateService } from '@ngx-translate/core';

@Component({
    templateUrl: 'add-edit-dca-plan.component.html',
    imports: [
        ReactiveFormsModule,
        RouterLink,
        MatIcon,
        InputValidatorComponent,
        SaveButtonComponent,
        TranslatePipe,
        SelectComponent,
        DateInputComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AddEditDcaPlanComponent extends BaseAddEditForm implements OnInit {
    private readonly dcaPlanService = inject(DcaPlanService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly assetService = inject(AssetService);
    private readonly groupService = inject(GroupService);
    private readonly strategyService = inject(StrategyService);
    private readonly currencyService = inject(CurrencyService);
    private readonly translateService = inject(TranslateService);
    private readonly router = inject(Router);

    private currentPortfolioId: number | null = null;

    protected readonly assets = signal<SelectItem<number, string>[]>([]);
    protected readonly groups = signal<SelectItem<number, string>[]>([]);
    protected readonly strategies = signal<SelectItem<number, string>[]>([]);
    protected readonly currencies = signal<SelectItem<number, string>[]>([]);

    protected readonly targetTypes: SelectItem<DcaPlanTargetTypeEnum, string>[] = [];

    public async ngOnInit(): Promise<void> {
        this.loading.set(true);

        this.initializeIdFromRoute();

        this.targetTypes.push(
            {
                key: DcaPlanTargetTypeEnum.Portfolio,
                label: this.translateService.instant('app.dcaPlans.targetType.portfolio'),
            },
            {
                key: DcaPlanTargetTypeEnum.Asset,
                label: this.translateService.instant('app.dcaPlans.targetType.asset'),
            },
            {
                key: DcaPlanTargetTypeEnum.Group,
                label: this.translateService.instant('app.dcaPlans.targetType.group'),
            },
            {
                key: DcaPlanTargetTypeEnum.Strategy,
                label: this.translateService.instant('app.dcaPlans.targetType.strategy'),
            },
        );

        this.form = this.formBuilder.group({
            targetType: [DcaPlanTargetTypeEnum.Portfolio, Validators.required],
            assetId: [null],
            groupId: [null],
            strategyId: [null],
            amount: ['', [Validators.required, Validators.min(0.01)]],
            currencyId: [null, Validators.required],
            intervalMonths: [1, [Validators.required, Validators.min(1)]],
            startDate: [null, Validators.required],
            endDate: [null],
        });

        const id = this.id();
        if (id !== null) {
            const plan = await this.dcaPlanService.getDcaPlan(id);
            await Promise.all([this.loadTargetOptions(plan.portfolioId), this.loadCurrencies()]);
            this.form.patchValue(plan);
        } else {
            const currentPortfolio = await this.portfolioService.getCurrentPortfolio();
            this.currentPortfolioId = currentPortfolio.id;
            await Promise.all([this.loadTargetOptions(currentPortfolio.id), this.loadCurrencies()]);
        }

        this.loading.set(false);
    }

    private async loadCurrencies(): Promise<void> {
        const currencies = await this.currencyService.getCurrencies();
        this.currencies.set(currencies.map((c: Currency) => ({ key: c.id, label: `${c.code} (${c.symbol})` })));
    }

    private async loadTargetOptions(portfolioId: number): Promise<void> {
        const [assets, groups, strategies] = await Promise.all([
            this.assetService.getAssets(portfolioId),
            this.groupService.getGroups(portfolioId),
            this.strategyService.getStrategies(portfolioId),
        ]);

        this.assets.set(assets.map((a: Asset) => ({ key: a.id, label: a.ticker.name })));
        this.groups.set(groups.map((g: Group) => ({ key: g.id, label: g.name })));
        this.strategies.set(strategies.map((s: Strategy) => ({ key: s.id, label: s.name })));
    }

    protected get targetType(): DcaPlanTargetTypeEnum {
        return this.form.get('targetType')?.value as DcaPlanTargetTypeEnum ?? DcaPlanTargetTypeEnum.Portfolio;
    }

    protected readonly DcaPlanTargetTypeEnum = DcaPlanTargetTypeEnum;

    public onSubmit(): void {
        this.submitted.set(true);
        this.alertService.clear();

        if (this.form.invalid) {
            return;
        }

        this.saving.set(true);
        try {
            if (this.id() === null) {
                this.createPlan();
            } else {
                this.updatePlan();
            }
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.saving.set(false);
        }
    }

    private async createPlan(): Promise<void> {
        if (this.currentPortfolioId === null) {
            return;
        }

        await this.dcaPlanService.createDcaPlan(this.currentPortfolioId, this.form.value);

        this.alertService.success('DCA plan added successfully', { keepAfterRouteChange: true });
        this.dcaPlanService.notify();
        this.router.navigate([this.routerBackLink()], { relativeTo: this.route });
    }

    private async updatePlan(): Promise<void> {
        const id = this.id();
        if (id === null) {
            return;
        }

        await this.dcaPlanService.updateDcaPlan(id, this.form.value);

        this.alertService.success('DCA plan updated successfully', { keepAfterRouteChange: true });
        this.dcaPlanService.notify();
        this.router.navigate([this.routerBackLink()], { relativeTo: this.route });
    }
}
