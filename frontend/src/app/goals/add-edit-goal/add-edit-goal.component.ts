import {
    ChangeDetectionStrategy,
    Component, inject, OnInit, signal,
} from '@angular/core';
import { ReactiveFormsModule, Validators } from '@angular/forms';
import { MatIcon } from '@angular/material/icon';
import { Router, RouterLink } from '@angular/router';
import { Portfolio } from '@app/models';
import { GoalTypeEnum } from '@app/models/enums/goal-type-enum';
import { PortfolioService } from '@app/services';
import { GoalService } from '@app/services/goal.service';
import { DateInputComponent } from '@app/shared/components/date-input/date-input.component';
import { BaseAddEditForm } from '@app/shared/components/form/base-add-edit-form';
import { InputValidatorComponent } from '@app/shared/components/input-validator/input-validator.component';
import { SaveButtonComponent } from '@app/shared/components/save-button/save-button.component';
import { SelectComponent } from '@app/shared/components/select/select.component';
import { SelectItem } from '@app/shared/types/select-item';
import { TranslateService } from '@ngx-translate/core';
import { TranslatePipe } from '@ngx-translate/core';

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
    private readonly translateService = inject(TranslateService);
    private readonly router = inject(Router);

    protected readonly portfolios = signal<SelectItem<number, string>[]>([]);

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
        });

        await this.loadPortfolios();

        const id = this.id();
        if (id !== null) {
            const goal = await this.goalService.getGoal(id);
            this.form.patchValue(goal);
        }

        this.loading.set(false);
    }

    private async loadPortfolios(): Promise<void> {
        const portfolios = await this.portfolioService.getPortfolios();
        this.portfolios.set(portfolios.map((p: Portfolio) => ({ key: p.id, label: p.name })));
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

    private async createGoal(): Promise<void> {
        await this.goalService.createGoal(this.form.value);

        this.alertService.success('Goal added successfully', { keepAfterRouteChange: true });
        this.goalService.notify();
        this.router.navigate([this.routerBackLink()], { relativeTo: this.route });
    }

    private async updateGoal(): Promise<void> {
        const id = this.id();
        if (id === null) {
            return;
        }

        await this.goalService.updateGoal(id, this.form.value);

        this.alertService.success('Update successful', { keepAfterRouteChange: true });
        this.goalService.notify();
        this.router.navigate([this.routerBackLink()], { relativeTo: this.route });
    }
}
