import { Component, Input, OnInit } from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { first } from 'rxjs/operators';

import * as moment from "moment";
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { BaseDialog } from '../../../shared/components/dialog/base-dialog';
import {Broker, Currency} from "@app/models";
import {AlertService, BrokerService, CurrencyService, DividendService} from "@app/services";

@Component({ templateUrl: 'dividend-dialog.component.html' })
export class DividendDialogComponent extends BaseDialog implements OnInit {
    @Input() public id: number;
    @Input() public assetId: number;
    public brokers: Broker[];
    public currencies: Map<number, Currency>;

    public constructor(
        private dividendService: DividendService,
        private brokerService: BrokerService,
        private currencyService: CurrencyService,
        formBuilder: UntypedFormBuilder,
        activeModal: NgbActiveModal,
        alertService: AlertService
    ) {
        super(activeModal, formBuilder, alertService);
    }

    public async ngOnInit(): Promise<void> {
        this.isAddMode = !this.id;

        const currentDate = moment().format('YYYY-MM-DDTHH:mm');

        this.brokerService.findAll()
            .pipe(first())
            .subscribe(brokers => {
                this.brokers = brokers;
                if (this.isAddMode) {
                    this.f['brokerId'].patchValue(brokers[0].id);
                }
            });

        this.currencies = await this.currencyService.getCurrencies();
        if (this.isAddMode) {
            this.f['currencyId'].patchValue(this.currencies.get(1)?.id);
        }

        this.form = this.formBuilder.group({
            assetId: [this.assetId, Validators.required],
            brokerId: ['', Validators.required],
            paidDate: [currentDate, Validators.required],
            priceGross: ['0.00', Validators.required],
            tax: ['0.00', Validators.required],
            priceNet: ['0.00', Validators.required],
            currencyId: ['', Validators.required],
            exchangeRate: ['', Validators.required],
        });

        if (!this.isAddMode) {
            this.dividendService.getById(this.id)
                .pipe(first())
                .subscribe(dividend => {
                    this.form.patchValue(dividend);
                    this.f['paidDate'].patchValue(moment(dividend.paidDate).format('YYYY-MM-DDTHH:mm'));
                });
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
            this.createDividend();
        } else {
            this.updateDividend();
        }
    }

    private createDividend(): void {
        this.dividendService.create(this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Asset added successfully', { keepAfterRouteChange: true });
                    this.activeModal.dismiss();
                    this.dividendService.notify();
                },
                error: error => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }

    private updateDividend(): void {
        this.dividendService.update(this.id, this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Update successful', { keepAfterRouteChange: true });
                    this.activeModal.dismiss();
                    this.dividendService.notify();
                },
                error: error => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }
}
