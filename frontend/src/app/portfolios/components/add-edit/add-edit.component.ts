import {
    Component, OnInit, signal, WritableSignal
} from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { Currency } from '@app/models';
import { AlertService, CurrencyService, PortfolioService } from '@app/services';
import { BaseForm } from '@app/shared/components/form/base-form';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({ templateUrl: 'add-edit.component.html' })
export class AddEditComponent extends BaseForm implements OnInit {
    public id: WritableSignal<number | null> = signal<number | null>(null);

    public currencies: Currency[];

    public constructor(
        private readonly portfolioService: PortfolioService,
        private readonly currencyService: CurrencyService,
        public activeModal: NgbActiveModal,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {
        super(formBuilder, alertService);
    }

    public async ngOnInit(): Promise<void> {
        this.form = this.formBuilder.group({
            name: ['My Portfolio', Validators.required],
            currencyId: ['', Validators.required],
            isDefault: [false, Validators.required],
        });

        this.currencies = await this.currencyService.getCurrencies();
        this.f['defaultCurrencyId'].patchValue(this.currencies[0].id);

        const id = this.id();
        if (id !== null) {
            const portfolio = await this.portfolioService.getPortfolio(id);
            this.form.patchValue(portfolio);
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
        if (this.id() === null) {
            this.createPortfolio();
        } else {
            this.updatePortfolio();
        }
    }

    private async createPortfolio(): Promise<void> {
        try {
            await this.portfolioService.createPortfolio(this.form.value);

            this.alertService.success('Group added successfully', { keepAfterRouteChange: true });
            this.activeModal.dismiss();
            this.portfolioService.notify();
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
            this.loading = false;
        }
    }

    private async updatePortfolio(): Promise<void> {
        const id = this.id();
        if (id === null) {
            return;
        }

        try {
            await this.portfolioService.updatePortfolio(id, this.form.value);

            this.alertService.success('Update successful', { keepAfterRouteChange: true });
            this.activeModal.dismiss();
            this.portfolioService.notify();
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
            this.loading = false;
        }
    }
}
