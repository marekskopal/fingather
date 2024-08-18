import { formatDate } from '@angular/common';
import {Component, computed, inject, OnInit, signal} from '@angular/core';
import { Validators } from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {
    Transaction, TransactionActionType
} from '@app/models';
import {
    AssetService,
    BrokerService,
    CurrencyService,
    PortfolioService,
    TransactionService
} from '@app/services';
import { BaseForm } from '@app/shared/components/form/base-form';
import {SelectItem} from "@app/shared/types/select-item";

@Component({
    template: '',
})
export abstract class AddEditBaseFormComponent extends BaseForm implements OnInit {
    private readonly transactionService = inject(TransactionService);
    private readonly assetService = inject(AssetService);
    private readonly brokerService = inject(BrokerService);
    private readonly currencyService = inject(CurrencyService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly route = inject(ActivatedRoute);
    private readonly router = inject(Router);

    protected readonly $id = signal<number | null>(null);
    protected assets: SelectItem<number, string>[] | null = null;
    protected assetId: number | null = null;
    protected brokers: SelectItem<number, string>[] = [];
    protected currencies: SelectItem<number, string>[] = [];

    protected readonly $routerLink = computed<string>(() => {
        return this.$id() !== null ? '../..' : '..';
    });

    public async ngOnInit(): Promise<void> {
        this.$loading.set(true);

        if (this.route.snapshot.params['id'] !== undefined) {
            this.$id.set(this.route.snapshot.params['id']);
        }

        const currentDate = formatDate((new Date()), 'y-MM-ddTHH:mm', 'en');

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const assets = await this.assetService.getAssets(portfolio.id);
        this.assets = assets.map((asset) => {
            return {
                key: asset.id,
                label: asset.ticker.name,
            }
        });

        const brokers = await this.brokerService.getBrokers(portfolio.id);
        this.brokers = brokers.map((broker) => {
            return {
                key: broker.id,
                label: broker.name,
            }
        });

        const currencies = await this.currencyService.getCurrencies();
        this.currencies = currencies.map((currency) => {
            return {
                key: currency.id,
                label: currency.code,
            }
        });

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

        const id = this.$id();
        if (id !== null) {
            const transaction = await this.transactionService.getTransaction(id);

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

            const id = this.$id();
            if (id === null) {
                const portfolio = await this.portfolioService.getCurrentPortfolio();
                this.createTransaction(portfolio.id);
            } else {
                this.updateTransaction(id);
            }
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.$saving.set(false);
        }
    }

    protected abstract processCreateTransaction(portfolioId: number): Transaction;
    protected abstract processUpdateTransaction(id: number): Transaction;

    private async createTransaction(portfolioId: number): Promise<void> {
        const transaction = this.processCreateTransaction(portfolioId);

        await this.transactionService.createTransaction(transaction, portfolioId);

        this.alertService.success('Dividend added successfully');
        this.router.navigate(['../'], { relativeTo: this.route });
        this.transactionService.notify();
    }

    private async updateTransaction(id: number): Promise<void> {
        const transaction = this.processUpdateTransaction(id);

        await this.transactionService.updateTransaction(id, transaction);

        this.alertService.success('Update successful');
        this.router.navigate(['../'], { relativeTo: this.route });
        this.transactionService.notify();
    }
}
