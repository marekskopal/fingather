import { Component, Input, OnInit } from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { AssetWithProperties, Group } from '@app/models';
import {
    AlertService, AssetService, GroupService, PortfolioService
} from '@app/services';
import { BaseForm } from '@app/shared/components/form/base-form';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { first } from 'rxjs/operators';

@Component({ templateUrl: 'add-edit.component.html' })
export class AddEditComponent extends BaseForm implements OnInit {
    @Input() public id: number;
    public isAddMode: boolean;
    public assets: AssetWithProperties[];
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
        this.isAddMode = !this.id;

        this.form = this.formBuilder.group({
            name: ['', Validators.required],
            color: ['#64ee85', Validators.required],
            assetIds: ['', Validators.required],
        });

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        this.assetService.getOpenedAssets(portfolio.id)
            .subscribe((assets) => {
                this.assets = assets;
            });

        this.groupService.getOthersGroup(portfolio.id)
            .subscribe((group) => {
                this.othersGroup = group;
            });

        if (!this.isAddMode) {
            this.groupService.getGroup(this.id)
                .pipe(first())
                .subscribe((x) => this.form.patchValue(x));
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
        if (this.isAddMode) {
            this.createGroup(portfolio.id);
        } else {
            this.updateGroup();
        }
    }

    private createGroup(portfolioId: number): void {
        this.groupService.createGroup(this.form.value, portfolioId)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Group added successfully', { keepAfterRouteChange: true });
                    this.activeModal.dismiss();
                    this.groupService.notify();
                },
                error: (error) => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }

    private updateGroup(): void {
        this.groupService.updateGroup(this.id, this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Update successful', { keepAfterRouteChange: true });
                    this.activeModal.dismiss();
                    this.groupService.notify();
                },
                error: (error) => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }
}
