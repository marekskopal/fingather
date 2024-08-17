import { formatDate } from '@angular/common';
import {ChangeDetectionStrategy, Component, inject, OnInit} from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
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
    templateUrl: 'add-edit-dividend-form.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AddEditDividendFormComponent extends BaseForm implements OnInit {
    private readonly transactionService = inject(TransactionService);
    private readonly assetService = inject(AssetService);
    private readonly brokerService = inject(BrokerService);
    private readonly currencyService = inject(CurrencyService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly route = inject(ActivatedRoute);
    private readonly router = inject(Router);

    public id: number | null = null;
    public assets: Asset[] | null;
    public assetId: number | null = null;
    public brokers: Broker[];
    public currencies: Currency[];

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
            const transaction = await this.transactionService.getTransaction(this.id);
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

        this.$saving.set(true);
        try {
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
        values.units = '0';
        values.actionType = TransactionActionType.Dividend;
        values.price = values.price.toString();
        values.tax = values.tax.toString();
        values.fee = values.fee.toString();

        await this.transactionService.createTransaction(values, portfolioId);

        this.alertService.success('Dividend added successfully');
        this.router.navigate(['../'], { relativeTo: this.route });
        this.transactionService.notify();
    }

    private async updateTransaction(id: number): Promise<void> {
        const values = this.form.value;
        values.actionCreated = (new Date(values.actionCreated)).toJSON();
        values.units = '0';
        values.actionType = TransactionActionType.Dividend;
        values.price = values.price.toString();
        values.tax = values.tax.toString();
        values.fee = values.fee.toString();

        await this.transactionService.updateTransaction(id, values);

        this.alertService.success('Update successful');
        this.router.navigate(['../'], { relativeTo: this.route });
        this.transactionService.notify();
    }
}
