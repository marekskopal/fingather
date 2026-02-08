import {
    ChangeDetectionStrategy,
    Component, DestroyRef, inject, OnInit, signal,
} from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { ReactiveFormsModule, UntypedFormArray, UntypedFormGroup, Validators } from '@angular/forms';
import { MatIcon } from '@angular/material/icon';
import { Router, RouterLink } from '@angular/router';
import { Asset } from '@app/models';
import { Group } from '@app/models';
import { AssetService, GroupService, PortfolioService, StrategyService } from '@app/services';
import { BaseAddEditForm } from '@app/shared/components/form/base-add-edit-form';
import { InputValidatorComponent } from '@app/shared/components/input-validator/input-validator.component';
import { PortfolioSelectorComponent } from '@app/shared/components/portfolio-selector/portfolio-selector.component';
import { SaveButtonComponent } from '@app/shared/components/save-button/save-button.component';
import { SelectComponent } from '@app/shared/components/select/select.component';
import { SelectItem } from '@app/shared/types/select-item';
import { TranslatePipe, TranslateService } from '@ngx-translate/core';

@Component({
    templateUrl: 'add-edit-strategy.component.html',
    imports: [
        TranslatePipe,
        PortfolioSelectorComponent,
        RouterLink,
        MatIcon,
        ReactiveFormsModule,
        InputValidatorComponent,
        SaveButtonComponent,
        SelectComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AddEditStrategyComponent extends BaseAddEditForm implements OnInit {
    private readonly strategyService = inject(StrategyService);
    private readonly assetService = inject(AssetService);
    private readonly groupService = inject(GroupService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly router = inject(Router);
    private readonly destroyRef = inject(DestroyRef);
    private readonly translateService = inject(TranslateService);

    protected assets: Asset[] = [];
    protected groups: Group[] = [];
    protected typeItems: SelectItem<string, string>[] = [];
    protected assetItems: SelectItem<number, string>[] = [];
    protected groupItems: SelectItem<number, string>[] = [];
    protected readonly isFirstStrategy = signal<boolean>(false);

    public get items(): UntypedFormArray {
        return this.form.get('items') as UntypedFormArray;
    }

    public async ngOnInit(): Promise<void> {
        this.loading.set(true);

        this.initializeIdFromRoute();

        this.form = this.formBuilder.group({
            name: ['', Validators.required],
            isDefault: [false],
            items: this.formBuilder.array([]),
        });

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const [assets, groups, strategies] = await Promise.all([
            this.assetService.getAssets(portfolio.id),
            this.groupService.getGroups(portfolio.id),
            this.strategyService.getStrategies(portfolio.id),
        ]);

        this.assets = assets;
        this.groups = groups;

        this.typeItems = [
            { key: 'asset', label: this.translateService.instant('app.strategies.addEdit.typeAsset') },
            { key: 'group', label: this.translateService.instant('app.strategies.addEdit.typeGroup') },
        ];
        this.assetItems = assets.map(asset => ({ key: asset.id, label: asset.ticker.name }));
        this.groupItems = groups.map(group => ({ key: group.id, label: group.name }));

        const id = this.id();
        if (id === null && strategies.length === 0) {
            this.isFirstStrategy.set(true);
            this.form.patchValue({ isDefault: true });
        }

        if (id !== null) {
            const strategy = await this.strategyService.getStrategy(id);
            this.form.patchValue({
                name: strategy.name,
                isDefault: strategy.isDefault,
            });

            for (const item of strategy.items) {
                if (item.isOthers) {
                    continue;
                }

                const type = item.assetId !== null ? 'asset' : 'group';
                this.items.push(this.createItemFormGroup(type, item.assetId, item.groupId, item.percentage));
            }
        }

        this.loading.set(false);
    }

    protected addItem(): void {
        this.items.push(this.createItemFormGroup('asset', null, null, 0));
    }

    protected removeItem(index: number): void {
        this.items.removeAt(index);
    }

    protected getOthersPercentage(): number {
        return Math.round((100 - this.getTotalPercentage()) * 100) / 100;
    }

    protected getTotalPercentage(): number {
        let total = 0;
        for (const item of this.items.controls) {
            total += Number(item.get('percentage')?.value) || 0;
        }
        return Math.round(total * 100) / 100;
    }

    public async onSubmit(): Promise<void> {
        this.submitted.set(true);

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        this.alertService.clear();

        if (this.form.invalid) {
            return;
        }

        const totalPercentage = this.getTotalPercentage();
        if (totalPercentage > 100) {
            this.alertService.error('Total percentage must not exceed 100%');
            return;
        }

        this.saving.set(true);
        try {
            const formValue = this.form.value;
            const strategyData = {
                name: formValue.name,
                isDefault: formValue.isDefault || this.isFirstStrategy(),
                items: formValue.items.map((item: {
                    type: string; assetId: number | null;
                    groupId: number | null; percentage: number;
                }) => ({
                    assetId: item.type === 'asset' ? item.assetId : null,
                    groupId: item.type === 'group' ? item.groupId : null,
                    isOthers: false,
                    percentage: item.percentage,
                })),
            };

            if (this.id() === null) {
                await this.strategyService.createStrategy(strategyData, portfolio.id);
                this.alertService.success('Strategy added successfully', { keepAfterRouteChange: true });
            } else {
                await this.strategyService.updateStrategy(this.id()!, strategyData);
                this.alertService.success('Update successful', { keepAfterRouteChange: true });
            }

            this.strategyService.notify();
            this.router.navigate([this.routerBackLink()], { relativeTo: this.route });
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.saving.set(false);
        }
    }

    private getTotalPercentageExcept(excludeGroup: UntypedFormGroup): number {
        let total = 0;
        for (const item of this.items.controls) {
            if (item === excludeGroup) {
                continue;
            }
            total += Number(item.get('percentage')?.value) || 0;
        }
        return Math.round(total * 100) / 100;
    }

    private createItemFormGroup(
        type: string,
        assetId: number | null,
        groupId: number | null,
        percentage: number,
    ): UntypedFormGroup {
        const group = this.formBuilder.group({
            type: [type, Validators.required],
            assetId: [assetId],
            groupId: [groupId],
            percentage: [percentage, [Validators.required, Validators.min(0), Validators.max(100)]],
        });

        group.get('type')?.valueChanges.pipe(
            takeUntilDestroyed(this.destroyRef),
        ).subscribe((newType: string) => {
            if (newType === 'asset') {
                group.patchValue({ groupId: null });
            } else if (newType === 'group') {
                group.patchValue({ assetId: null });
            }
        });

        group.get('percentage')?.valueChanges.pipe(
            takeUntilDestroyed(this.destroyRef),
        ).subscribe((value: number) => {
            const othersTotal = this.getTotalPercentageExcept(group);
            const maxAllowed = Math.round((100 - othersTotal) * 100) / 100;
            if (value > maxAllowed) {
                group.get('percentage')?.setValue(maxAllowed, { emitEvent: false });
            }
        });

        return group;
    }
}
