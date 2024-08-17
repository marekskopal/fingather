import {
    ChangeDetectionStrategy,
    Component, inject, OnInit, signal, WritableSignal
} from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { Asset, Group } from '@app/models';
import {
    AlertService, AssetService, GroupService, PortfolioService
} from '@app/services';
import {BaseDialog} from "@app/shared/components/dialog/base-dialog";

@Component({
    templateUrl: 'add-edit.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AddEditComponent extends BaseDialog implements OnInit {
    private readonly assetService = inject(AssetService);
    private readonly groupService = inject(GroupService);
    private readonly portfolioService = inject(PortfolioService);

    public id: WritableSignal<number | null> = signal<number | null>(null);

    public assets: Asset[];
    public othersGroup: Group;

    public async ngOnInit(): Promise<void> {
        this.form = this.formBuilder.group({
            name: ['', Validators.required],
            color: ['#64ee85', Validators.required],
            assetIds: ['', Validators.required],
        });

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        this.assets = await this.assetService.getAssets(portfolio.id);

        this.othersGroup = await this.groupService.getOthersGroup(portfolio.id);

        const id = this.id();
        if (id !== null) {
            const group = await this.groupService.getGroup(id);
            this.form.patchValue(group);
        }
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
            if (this.id() === null) {
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
        this.activeModal.dismiss();
        this.groupService.notify();
    }

    private async updateGroup(): Promise<void> {
        const id = this.id();
        if (id === null) {
            return;
        }

        await this.groupService.updateGroup(id, this.form.value);

        this.alertService.success('Update successful', { keepAfterRouteChange: true });
        this.activeModal.dismiss();
        this.groupService.notify();
    }
}
