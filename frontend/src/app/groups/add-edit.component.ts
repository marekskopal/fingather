import {Component, Input, OnInit} from '@angular/core';
import {UntypedFormBuilder, Validators} from '@angular/forms';
import {first} from 'rxjs/operators';

import {AlertService, AssetService, GroupService} from '@app/services';
import {NgbActiveModal} from "@ng-bootstrap/ng-bootstrap";
import {AssetWithProperties, Group} from "../models";
import {BaseForm} from "@app/shared/components/form/base-form";

@Component({ templateUrl: 'add-edit.component.html' })
export class AddEditComponent extends BaseForm implements OnInit {
    @Input() public id: number;
    public isAddMode: boolean;
    public assets: AssetWithProperties[];
    public othersGroup: Group;

    public constructor(
        private assetService: AssetService,
        private groupService: GroupService,
        public activeModal: NgbActiveModal,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {
        super(formBuilder, alertService)
    }

    public ngOnInit(): void {
        this.isAddMode = !this.id;

        this.form = this.formBuilder.group({
            name: ['', Validators.required],
            assetIds: ['', Validators.required],
        });

        this.assetService.getOpenedAssets()
            .subscribe((assets) => {
                this.assets = assets;
            });

        this.groupService.getOthersGroup()
            .subscribe((group) => {
                this.othersGroup = group;
            });

        if (!this.isAddMode) {
            this.groupService.getGroup(this.id)
                .pipe(first())
                .subscribe(x => this.form.patchValue(x));
        }
    }

    public onSubmit(): void {
        this.submitted = true;

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.loading = true;
        if (this.isAddMode) {
            this.createGroup();
        } else {
            this.updateGroup();
        }
    }

    private createGroup(): void {
        this.groupService.createGroup(this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Group added successfully', { keepAfterRouteChange: true });
                    this.activeModal.dismiss();
                    this.groupService.notify();
                },
                error: error => {
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
                error: error => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }
}
