import {
    ChangeDetectionStrategy,
    Component, inject, OnInit, signal,
} from '@angular/core';
import { ReactiveFormsModule, Validators } from '@angular/forms';
import { MatIcon } from '@angular/material/icon';
import { Router, RouterLink } from '@angular/router';
import { DcaPlan, Portfolio } from '@app/models';
import { GoalTypeEnum } from '@app/models/enums/goal-type-enum';
import { DcaPlanService, PortfolioService } from '@app/services';
import { GoalService } from '@app/services/goal.service';
import { DateInputComponent } from '@app/shared/components/date-input/date-input.component';
import { BaseAddEditForm } from '@app/shared/components/form/base-add-edit-form';
import { InputValidatorComponent } from '@app/shared/components/input-validator/input-validator.component';
import { SaveButtonComponent } from '@app/shared/components/save-button/save-button.component';
import { SelectComponent } from '@app/shared/components/select/select.component';
import { SelectItem } from '@app/shared/types/select-item';
import { TranslateService } from '@ngx-translate/core';
import { TranslatePipe } from '@ngx-translate/core';

const NO_DCA_PLAN_KEY = 0;

@Component({
    templateUrl: 'add-edit-goal.component.html',
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
export class AddEditGoalComponent extends BaseAddEditForm implements OnInit {
    private readonly goalService = inject(GoalService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly dcaPlanService = inject(DcaPlanService);
    private readonly translateService = inject(TranslateService);
    private readonly router = inject(Router);

    protected readonly portfolios = signal<SelectItem<number, string>[]>([]);
    protected readonly dcaPlans = signal<SelectItem<number, string>[]>([]);

    protected types: SelectItem<GoalTypeEnum, string>[] = [];

    public async ngOnInit(): Promise<void> {
        this.loading.set(true);

        this.initializeIdFromRoute();

        this.types = [
            {
                key: GoalTypeEnum.PortfolioValue,
                label: this.translateService.instant('app.goals.type.portfolioValue'),
            },
            {
                key: GoalTypeEnum.ReturnPercentage,
                label: this.translateService.instant('app.goals.type.returnPercentage'),
            },
            {
                key: GoalTypeEnum.InvestedAmount,
                label: this.translateService.instant('app.goals.type.investedAmount'),
            },
        ];

        this.form = this.formBuilder.group({
            portfolioId: [null, Validators.required],
            type: [GoalTypeEnum.PortfolioValue, Validators.required],
            targetValue: ['', Validators.required],
            deadline: [null],
            isActive: [true],
            dcaPlanId: [NO_DCA_PLAN_KEY],
        });

        this.form.get('portfolioId')!.valueChanges.subscribe(async (portfolioId: number | null) => {
            if (portfolioId !== null) {
                await this.loadDcaPlans(portfolioId);
            } else {
                this.dcaPlans.set([]);
            }
        });

        await this.loadPortfolios();

        const id = this.id();
        if (id !== null) {
            const goal = await this.goalService.getGoal(id);
            this.form.patchValue({
                ...goal,
                dcaPlanId: goal.dcaPlanId ?? NO_DCA_PLAN_KEY,
            });

            if (goal.portfolioId) {
                await this.loadDcaPlans(goal.portfolioId);
            }
        }

        this.loading.set(false);
    }

    private async loadPortfolios(): Promise<void> {
        const portfolios = await this.portfolioService.getPortfolios();
        this.portfolios.set(portfolios.map((p: Portfolio) => ({ key: p.id, label: p.name })));
    }

    private async loadDcaPlans(portfolioId: number): Promise<void> {
        const plans = await this.dcaPlanService.getDcaPlans(portfolioId);
        this.dcaPlans.set([
            { key: NO_DCA_PLAN_KEY, label: this.translateService.instant('app.goals.addEdit.noDcaPlan') },
            ...plans.map((p: DcaPlan) => ({ key: p.id, label: p.targetName })),
        ]);
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
                this.createGoal();
            } else {
                this.updateGoal();
            }
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.saving.set(false);
        }
    }

    private buildFormPayload(): object {
        const value = this.form.value;
        return {
            ...value,
            dcaPlanId: value.dcaPlanId !== NO_DCA_PLAN_KEY ? value.dcaPlanId : null,
        };
    }

    private async createGoal(): Promise<void> {
        await this.goalService.createGoal(this.form.value.portfolioId, this.buildFormPayload());

        this.alertService.success('Goal added successfully', { keepAfterRouteChange: true });
        this.goalService.notify();
        this.router.navigate([this.routerBackLink()], { relativeTo: this.route });
    }

    private async updateGoal(): Promise<void> {
        const id = this.id();
        if (id === null) {
            return;
        }

        await this.goalService.updateGoal(id, this.buildFormPayload());

        this.alertService.success('Update successful', { keepAfterRouteChange: true });
        this.goalService.notify();
        this.router.navigate([this.routerBackLink()], { relativeTo: this.route });
    }
}
