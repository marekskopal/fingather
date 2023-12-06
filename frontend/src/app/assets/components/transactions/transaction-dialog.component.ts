import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { first } from 'rxjs/operators';

import {AlertService, AssetService, BrokerService, TransactionService} from '@app/_services';
import {Asset, Broker} from "../../../_models";
import * as moment from "moment";
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({ templateUrl: 'transaction-dialog.component.html' })
export class TransactionDialogComponent implements OnInit {
    public form: UntypedFormGroup;
    public assetId: string;
    public asset: Asset;
    public id: string;
    public isAddMode: boolean;
    public loading = false;
    public submitted = false;
    public actionTypes = [
       {name: 'Buy', key: 'buy'},
       {name: 'Sell', key: 'sell'},
    ]
    public brokers: Broker[];

    constructor(
        private formBuilder: UntypedFormBuilder,
        private route: ActivatedRoute,
        private router: Router,
        private transactionService: TransactionService,
        private alertService: AlertService,
        private assetService: AssetService,
        private brokerService: BrokerService,
        public activeModal: NgbActiveModal,
    ) {}

    ngOnInit() {
        this.assetId = this.route.parent.snapshot.params['assetId'];

        this.id = this.route.snapshot.params['id'];
        this.isAddMode = !this.id;

        const currentDate = moment().format('YYYY-MM-DDTHH:mm');

        this.brokerService.findAll()
            .pipe(first())
            .subscribe(brokers => this.brokers = brokers);

        this.form = this.formBuilder.group({
            assetId: [this.assetId, Validators.required],
            brokerId: ['', Validators.required],
            actionType: ['buy', Validators.required],
            created: [currentDate, Validators.required],
            units: ['0.00', Validators.required],
            priceUnit: ['0.00', Validators.required],
        });

        if (!this.isAddMode) {
            this.transactionService.getByUuid(this.id)
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
            this.createTransaction();
        } else {
            this.updateTransaction();
        }
    }

    private createTransaction() {
        this.transactionService.create(this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Asset added successfully', { keepAfterRouteChange: true });
                    this.router.navigate(['../'], { relativeTo: this.route });
                },
                error: error => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }

    private updateTransaction() {
        this.transactionService.update(this.id, this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Update successful', { keepAfterRouteChange: true });
                    this.router.navigate(['../../'], { relativeTo: this.route });
                },
                error: error => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }
}
