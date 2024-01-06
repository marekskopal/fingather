﻿import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { first } from 'rxjs/operators';

import * as moment from "moment";
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import {Asset, Broker, Currency, TransactionActionType} from "@app/models";
import {AlertService, AssetService, BrokerService, CurrencyService, TransactionService} from "@app/services";
import {BaseForm} from "@app/shared/components/form/base-form";

@Component({ templateUrl: 'transaction-dialog.component.html' })
export class TransactionDialogComponent extends BaseForm implements OnInit {
    public id: number|null = null;
    public actionTypes: TransactionActionType[] = [
        TransactionActionType.Buy,
        TransactionActionType.Sell,
    ]
    public assets: Asset[];
    public brokers: Broker[];
    public currencies: Map<number, Currency>;

    public constructor(
        private route: ActivatedRoute,
        private router: Router,
        private transactionService: TransactionService,
        private assetService: AssetService,
        private brokerService: BrokerService,
        private currencyService: CurrencyService,
        public activeModal: NgbActiveModal,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {
        super(formBuilder, alertService)
    }

    public async ngOnInit(): Promise<void> {
        if (this.route.snapshot.params['id'] !== undefined) {
            this.id = this.route.snapshot.params['id'];
        }

        const currentDate = moment().format('YYYY-MM-DDTHH:mm');

        this.assetService.getAssets()
            .pipe(first())
            .subscribe((assets: Asset[]) => this.assets = assets);

        this.brokerService.getBrokers()
            .pipe(first())
            .subscribe((brokers: Broker[]) => this.brokers = brokers);

        this.currencies = await this.currencyService.getCurrencies();

        this.form = this.formBuilder.group({
            assetId: ['', Validators.required],
            brokerId: ['', Validators.required],
            actionType: ['buy', Validators.required],
            actionCreated: [currentDate, Validators.required],
            units: ['0.00', Validators.required],
            price: ['0.00', Validators.required],
            tax: ['0.00', Validators.required],
            currencyId: ['', Validators.required],
        });

        if (this.id !== null) {
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
        if (this.id === null) {
            this.createTransaction();
        } else {
            this.updateTransaction(this.id);
        }
    }

    private createTransaction(): void {
        this.transactionService.createTransaction(this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Asset added successfully');
                    this.activeModal.dismiss()
                    this.transactionService.notify();
                },
                error: error => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }

    private updateTransaction(id: number): void {
        this.transactionService.updateTransaction(id, this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Update successful');
                    this.activeModal.dismiss()
                    this.transactionService.notify();
                },
                error: error => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }
}
