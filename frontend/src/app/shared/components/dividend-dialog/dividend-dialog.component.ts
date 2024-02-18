import { Component, OnInit } from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import {
    Asset, Broker, Currency, Transaction, TransactionActionType
} from '@app/models';
import {
    AlertService,
    AssetService,
    BrokerService,
    CurrencyService,
    PortfolioService,
    TransactionService
} from '@app/services';
import { BaseForm } from '@app/shared/components/form/base-form';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import * as moment from 'moment';
import { first } from 'rxjs/operators';

@Component({ templateUrl: 'dividend-dialog.component.html' })
export class DividendDialogComponent extends BaseForm implements OnInit {
    public id: number | null = null;
    public assets: Asset[] | null;
    public assetId: number | null = null;
    public brokers: Broker[];
    public currencies: Currency[];

    public constructor(
        private readonly transactionService: TransactionService,
        private readonly assetService: AssetService,
        private readonly brokerService: BrokerService,
        private readonly currencyService: CurrencyService,
        private readonly portfolioService: PortfolioService,
        private route: ActivatedRoute,
        public activeModal: NgbActiveModal,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {
        super(formBuilder, alertService);
    }

    public async ngOnInit(): Promise<void> {
        if (this.route.snapshot.params['id'] !== undefined) {
            this.id = this.route.snapshot.params['id'];
        }

        const currentDate = moment().format('YYYY-MM-DDTHH:mm');

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        this.assetService.getAssets(portfolio.id)
            .pipe(first())
            .subscribe((assets: Asset[]) => this.assets = assets);

        this.brokerService.getBrokers(portfolio.id)
            .pipe(first())
            .subscribe((brokers: Broker[]) => this.brokers = brokers);

        this.currencyService.getCurrencies()
            .pipe(first())
            .subscribe((currencies: Currency[]) => this.currencies = currencies);

        const defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.form = this.formBuilder.group({
            assetId: [this.assetId !== null ? this.assetId : '', Validators.required],
            brokerId: ['', Validators.required],
            actionCreated: [currentDate, Validators.required],
            price: ['0.00', Validators.required],
            currencyId: ['', Validators.required],
            tax: ['0.00', Validators.required],
            taxCurrencyId: [defaultCurrency.id, Validators.required],
            fee: ['0.00', Validators.required],
            feeCurrencyId: [defaultCurrency.id, Validators.required],
        });

        if (this.id !== null) {
            this.transactionService.getTransaction(this.id)
                .pipe(first())
                .subscribe((transaction: Transaction) => {
                    transaction.actionCreated = moment(transaction.actionCreated).format('YYYY-MM-DDTHH:mm');
                    this.form.patchValue(transaction);
                });
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
        if (this.id === null) {
            const portfolio = await this.portfolioService.getCurrentPortfolio();
            this.createTransaction(portfolio.id);
        } else {
            this.updateTransaction(this.id);
        }
    }

    private createTransaction(portfolioId: number): void {
        const values = this.form.value;
        values.assetId = parseInt(values.assetId, 10);
        values.units = 0;
        values.actionType = TransactionActionType.Dividend;

        this.transactionService.createTransaction(values, portfolioId)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Dividend added successfully');
                    this.activeModal.dismiss();
                    this.transactionService.notify();
                },
                error: (error) => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }

    private updateTransaction(id: number): void {
        const values = this.form.value;
        values.actionCreated = (new Date(values.actionCreated)).toJSON();
        values.units = 0;
        values.actionType = TransactionActionType.Dividend;

        this.transactionService.updateTransaction(id, values)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Update successful');
                    this.activeModal.dismiss();
                    this.transactionService.notify();
                },
                error: (error) => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }
}
