import { Component, Input, OnInit } from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { AlertService, BrokerService, PortfolioService } from '@app/services';
import { BaseForm } from '@app/shared/components/form/base-form';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { first } from 'rxjs/operators';

import { BrokerImportTypes } from '../models';

@Component({ templateUrl: 'add-edit.component.html' })
export class AddEditComponent extends BaseForm implements OnInit {
    @Input() public id: number;
    public isAddMode: boolean;
    public importTypes = [
        { name: 'Trading212', key: BrokerImportTypes.Trading212 },
        { name: 'Revolut', key: BrokerImportTypes.Revolut },
        { name: 'Anycoin', key: BrokerImportTypes.Anycoin },
    ];

    public constructor(
        private readonly brokerService: BrokerService,
        private readonly portfolioService: PortfolioService,
        public activeModal: NgbActiveModal,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {
        super(formBuilder, alertService);
    }

    public ngOnInit(): void {
        this.isAddMode = !this.id;

        this.form = this.formBuilder.group({
            name: ['', Validators.required],
            importType: [BrokerImportTypes.Trading212, Validators.required],
        });

        if (!this.isAddMode) {
            this.brokerService.getBroker(this.id)
                .pipe(first())
                .subscribe((x) => this.form.patchValue(x));
        }
    }

    public async onSubmit(): Promise<void> {
        this.submitted = true;

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.loading = true;
        if (this.isAddMode) {
            const portfolio = await this.portfolioService.getCurrentPortfolio();
            this.createBroker(portfolio.id);
        } else {
            this.updateBroker();
        }
    }

    private createBroker(portfolioId: number): void {
        this.brokerService.createBroker(this.form.value, portfolioId)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Broker added successfully', { keepAfterRouteChange: true });
                    this.activeModal.dismiss();
                    this.brokerService.notify();
                },
                error: (error) => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }

    private updateBroker(): void {
        this.brokerService.updateBroker(this.id, this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Update successful', { keepAfterRouteChange: true });
                    this.activeModal.dismiss();
                    this.brokerService.notify();
                },
                error: (error) => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }
}
