import { formatDate } from '@angular/common';
import { ChangeDetectionStrategy, Component, OnInit } from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import {
    Asset, Broker, Currency, TransactionActionType
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

@Component({
    templateUrl: 'transaction-dialog.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TransactionDialogComponent extends BaseForm implements OnInit {
    public id: number | null = null;
    public actionTypes: TransactionActionType[] = [
        TransactionActionType.Buy,
        TransactionActionType.Sell,
    ];
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
        this.$loading.set(true);

        if (this.route.snapshot.params['id'] !== undefined) {
            this.id = this.route.snapshot.params['id'];
        }

        const currentDate = formatDate((new Date()), 'y-MM-ddTHH:mm', 'en');

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        this.assets = await this.assetService.getAssets(portfolio.id);

        this.brokers = await this.brokerService.getBrokers(portfolio.id);

        this.currencies = await this.currencyService.getCurrencies();

        const defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.form = this.formBuilder.group({
            assetId: [this.assetId !== null ? this.assetId : '', Validators.required],
            brokerId: [''],
            actionType: [TransactionActionType.Buy.toString(), Validators.required],
            actionCreated: [currentDate, Validators.required],
            units: ['0.00', Validators.required],
            price: ['0.00', Validators.required],
            currencyId: [defaultCurrency.id, Validators.required],
            tax: ['0.00', Validators.required],
            taxCurrencyId: [defaultCurrency.id, Validators.required],
            fee: ['0.00', Validators.required],
            feeCurrencyId: [defaultCurrency.id, Validators.required],
        });

        if (this.id !== null) {
            const transaction = await this.transactionService.getTransaction(this.id);

            if (transaction.brokerId === null) {
                transaction.brokerId = '';
            }

            transaction.actionCreated = formatDate(Date.parse(transaction.actionCreated), 'y-MM-ddTHH:mm', 'en');
            this.form.patchValue(transaction);
        }

        this.$loading.set(false);
    }

    public async onSubmit(): Promise<void> {
        this.$submitted.set(true);

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        try {
            this.$saving.set(true);

            if (this.id === null) {
                const portfolio = await this.portfolioService.getCurrentPortfolio();
                this.createTransaction(portfolio.id);
            } else {
                this.updateTransaction(this.id);
            }
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.$saving.set(false);
        }
    }

    private async createTransaction(portfolioId: number): Promise<void> {
        const values = this.form.value;
        values.assetId = parseInt(values.assetId, 10);
        values.units = values.units.toString();
        values.price = values.price.toString();
        values.tax = values.tax.toString();
        values.fee = values.fee.toString();

        if (values.brokerId === '') {
            values.brokerId = null;
        }

        await this.transactionService.createTransaction(values, portfolioId);

        this.alertService.success('Dividend added successfully');
        this.activeModal.dismiss();
        this.transactionService.notify();
    }

    private async updateTransaction(id: number): Promise<void> {
        const values = this.form.value;
        values.actionCreated = (new Date(values.actionCreated)).toJSON();
        values.units = values.units.toString();
        values.price = values.price.toString();
        values.tax = values.tax.toString();
        values.fee = values.fee.toString();

        if (values.brokerId === '') {
            values.brokerId = null;
        }

        await this.transactionService.updateTransaction(id, values);

        this.alertService.success('Update successful');
        this.activeModal.dismiss();
        this.transactionService.notify();
    }
}
