import {Component, Input, OnInit} from '@angular/core';
import {UntypedFormBuilder, UntypedFormGroup, Validators} from '@angular/forms';
import {first} from 'rxjs/operators';

import {AlertService, BrokerService} from '@app/services';
import {BrokerImportTypes} from "../models";
import {NgbActiveModal} from "@ng-bootstrap/ng-bootstrap";

@Component({ templateUrl: 'add-edit.component.html' })
export class AddEditComponent implements OnInit {
    public form: UntypedFormGroup;
    @Input() public id: number;
    public isAddMode: boolean;
    public loading = false;
    public submitted = false;
    public importTypes = [
        {name: 'Trading212', key: BrokerImportTypes.Trading212},
        {name: 'Revolut', key: BrokerImportTypes.Revolut},
        {name: 'Anycoin', key: BrokerImportTypes.Anycoin},
    ]

    public constructor(
        private formBuilder: UntypedFormBuilder,
        private brokerService: BrokerService,
        private alertService: AlertService,
        public activeModal: NgbActiveModal,
    ) {}

    public ngOnInit() {
        this.isAddMode = !this.id;

        this.form = this.formBuilder.group({
            name: ['', Validators.required],
            importType: [BrokerImportTypes.Trading212, Validators.required],
        });

        if (!this.isAddMode) {
            this.brokerService.getByUuid(this.id)
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
            this.createBroker();
        } else {
            this.updateBroker();
        }
    }

    private createBroker(): void {
        this.brokerService.create(this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Broker added successfully', { keepAfterRouteChange: true });
                    this.activeModal.dismiss();
                    this.brokerService.notify();
                },
                error: error => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }

    private updateBroker(): void {
        this.brokerService.update(this.id, this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Update successful', { keepAfterRouteChange: true });
                    this.activeModal.dismiss();
                    this.brokerService.notify();
                },
                error: error => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }
}
