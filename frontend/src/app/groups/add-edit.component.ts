import {Component, Input, OnInit} from '@angular/core';
import {UntypedFormBuilder, UntypedFormGroup, Validators} from '@angular/forms';
import {first} from 'rxjs/operators';

import {AlertService, AssetService, GroupService} from '@app/services';
import {NgbActiveModal} from "@ng-bootstrap/ng-bootstrap";
import {Asset, Group} from "../models";

@Component({ templateUrl: 'add-edit.component.html' })
export class AddEditComponent implements OnInit {
    public form: UntypedFormGroup;
    @Input() id: number;
    public isAddMode: boolean;
    public loading = false;
    public submitted = false;
    public assets: Asset[];
    public othersGroup: Group;

    constructor(
        private formBuilder: UntypedFormBuilder,
        private assetService: AssetService,
        private groupService: GroupService,
        private alertService: AlertService,
        public activeModal: NgbActiveModal,
    ) {}

    ngOnInit() {
        this.isAddMode = !this.id;

        this.form = this.formBuilder.group({
            name: ['', Validators.required],
            assetIds: ['', Validators.required],
        });

        this.assetService.findAll()
            .subscribe((assets) => {
                this.assets = assets;
            });

        this.groupService.getOthersGroup()
            .subscribe((group) => {
                this.othersGroup = group;
            });

        if (!this.isAddMode) {
            this.groupService.getById(this.id)
                .pipe(first())
                .subscribe(x => this.form.patchValue(x));
        }
    }

    // convenience getter for easy access to form fields
    get f() { return this.form.controls; }

    onSubmit() {
        this.submitted = true;

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.loading = true;
        if (this.isAddMode) {
            this.createBroker();
        } else {
            this.updateBroker();
        }
    }

    private createBroker() {
        this.groupService.create(this.form.value)
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

    private updateBroker() {
        this.groupService.update(this.id, this.form.value)
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
