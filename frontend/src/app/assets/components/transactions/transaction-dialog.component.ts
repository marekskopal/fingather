import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { first } from 'rxjs/operators';

import * as moment from "moment";
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import {AssetWithProperties, Broker} from "@app/models";
import {AlertService, BrokerService, TransactionService} from "@app/services";
import {BaseForm} from "@app/shared/components/form/base-form";

@Component({ templateUrl: 'transaction-dialog.component.html' })
export class TransactionDialogComponent extends BaseForm implements OnInit {
    public assetId: number;
    public asset: AssetWithProperties;
    public id: number;
    public isAddMode: boolean;
    public actionTypes = [
       {name: 'Buy', key: 'buy'},
       {name: 'Sell', key: 'sell'},
    ]
    public brokers: Broker[];

    public constructor(
        private route: ActivatedRoute,
        private router: Router,
        private transactionService: TransactionService,
        private brokerService: BrokerService,
        public activeModal: NgbActiveModal,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {
        super(formBuilder, alertService)
    }

    public ngOnInit(): void {
        const routeParent = this.route.parent;
        if (routeParent === null) {
            return;
        }

        this.assetId = routeParent.snapshot.params['assetId'];

        this.id = this.route.snapshot.params['id'];
        this.isAddMode = !this.id;

        const currentDate = moment().format('YYYY-MM-DDTHH:mm');

        this.brokerService.getBrokers()
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
            this.transactionService.getTransaction(this.id)
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
            this.createTransaction();
        } else {
            this.updateTransaction();
        }
    }

    private createTransaction(): void {
        this.transactionService.createTransaction(this.form.value)
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

    private updateTransaction(): void {
        this.transactionService.updateTransaction(this.id, this.form.value)
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
