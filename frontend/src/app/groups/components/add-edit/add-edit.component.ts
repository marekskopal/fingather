import {
    Component, OnInit, signal, WritableSignal
} from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { Asset, Group } from '@app/models';
import {
    AlertService, AssetService, GroupService, PortfolioService
} from '@app/services';
import { BaseForm } from '@app/shared/components/form/base-form';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({ templateUrl: 'add-edit.component.html' })
export class AddEditComponent extends BaseForm implements OnInit {
    public id: WritableSignal<number | null> = signal<number | null>(null);

    public assets: Asset[];
    public othersGroup: Group;

    public constructor(
        private readonly assetService: AssetService,
        private readonly groupService: GroupService,
        private readonly portfolioService: PortfolioService,
        public activeModal: NgbActiveModal,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {
        super(formBuilder, alertService);
    }

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
        this.submitted = true;

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.loading = true;
        if (this.id() === null) {
            this.createGroup(portfolio.id);
        } else {
            this.updateGroup();
        }
    }

    private async createGroup(portfolioId: number): Promise<void> {
        try {
            await this.groupService.createGroup(this.form.value, portfolioId);

            this.alertService.success('Group added successfully', { keepAfterRouteChange: true });
            this.activeModal.dismiss();
            this.groupService.notify();
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
            this.loading = false;
        }
    }

    private async updateGroup(): Promise<void> {
        const id = this.id();
        if (id === null) {
            return;
        }

        try {
            await this.groupService.updateGroup(id, this.form.value);

            this.alertService.success('Update successful', { keepAfterRouteChange: true });
            this.activeModal.dismiss();
            this.groupService.notify();
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
            this.loading = false;
        }
    }
}
