import {Component, Input, OnInit} from '@angular/core';
import {UntypedFormBuilder, UntypedFormGroup, Validators} from '@angular/forms';
import {first} from 'rxjs/operators';

import {AlertService, AssetService, GroupService} from '@app/services';
import {NgbActiveModal} from "@ng-bootstrap/ng-bootstrap";
import {Asset, Group} from "../models";

@Component({ templateUrl: 'add-edit.component.html' })
export class AddEditComponent implements OnInit {
    public form: UntypedFormGroup;
    @Input() public id: number;
    public isAddMode: boolean;
    public loading = false;
    public submitted = false;
    public assets: Asset[];
    public othersGroup: Group;

    public constructor(
        private formBuilder: UntypedFormBuilder,
        private assetService: AssetService,
        private groupService: GroupService,
        private alertService: AlertService,
        public activeModal: NgbActiveModal,
    ) {}

    public ngOnInit(): void {
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
    public get f() { return this.form.controls; }

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

    private updateGroup(): void {
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
