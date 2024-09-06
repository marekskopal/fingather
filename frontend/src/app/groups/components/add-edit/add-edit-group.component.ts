import {
    ChangeDetectionStrategy,
    Component, inject, OnInit
} from '@angular/core';
import {ReactiveFormsModule, Validators} from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import {ActivatedRoute, Router, RouterLink} from "@angular/router";
import { Group } from '@app/models';
import { AssetService, GroupService, PortfolioService
} from '@app/services';
import {ColorPickerComponent} from "@app/shared/components/color-picker/color-picker.component";
import {BaseAddEditForm} from "@app/shared/components/form/base-add-edit-form";
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import {SelectMultiComponent} from "@app/shared/components/select-multi/select-multi.component";
import {SelectItem} from "@app/shared/types/select-item";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'add-edit-group.component.html',
    standalone: true,
    imports: [
        TranslateModule,
        PortfolioSelectorComponent,
        RouterLink,
        MatIcon,
        ReactiveFormsModule,
        InputValidatorComponent,
        SaveButtonComponent,
        SelectMultiComponent,
        ColorPickerComponent
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AddEditGroupComponent extends BaseAddEditForm implements OnInit {
    private readonly assetService = inject(AssetService);
    private readonly groupService = inject(GroupService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly route = inject(ActivatedRoute);
    private readonly router = inject(Router);

    protected assets: SelectItem<number, string>[] = [];
    protected othersGroup: Group;

    public async ngOnInit(): Promise<void> {
        this.$loading.set(true);

        if (this.route.snapshot.params['id'] !== undefined) {
            this.$id.set(this.route.snapshot.params['id']);
        }

        this.form = this.formBuilder.group({
            name: ['', Validators.required],
            color: ['#64ee85', Validators.required],
            assetIds: ['', Validators.required],
        });

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const assets = await this.assetService.getAssets(portfolio.id);
        this.assets = assets.map((asset) => {
            return {
                key: asset.id,
                label: asset.ticker.name,
            }
        });

        this.othersGroup = await this.groupService.getOthersGroup(portfolio.id);

        const id = this.$id();
        if (id !== null) {
            const group = await this.groupService.getGroup(id);
            this.form.patchValue(group);
        }

        this.$loading.set(false);
    }

    public async onSubmit(): Promise<void> {
        this.$submitted.set(true);

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.$saving.set(true);
        try {
            if (this.$id() === null) {
                this.createGroup(portfolio.id);
            } else {
                this.updateGroup();
            }
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.$saving.set(false);
        }
    }

    private async createGroup(portfolioId: number): Promise<void> {
        await this.groupService.createGroup(this.form.value, portfolioId);

        this.alertService.success('Group added successfully', { keepAfterRouteChange: true });
        this.groupService.notify();
        this.router.navigate([this.$routerBackLink()], { relativeTo: this.route });
    }

    private async updateGroup(): Promise<void> {
        const id = this.$id();
        if (id === null) {
            return;
        }

        await this.groupService.updateGroup(id, this.form.value);

        this.alertService.success('Update successful', { keepAfterRouteChange: true });
        this.groupService.notify();
        this.router.navigate([this.$routerBackLink()], { relativeTo: this.route });
    }
}
