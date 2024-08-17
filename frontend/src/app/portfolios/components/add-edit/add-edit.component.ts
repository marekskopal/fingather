import {
    ChangeDetectionStrategy,
    Component, inject, OnInit, signal, WritableSignal
} from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { Currency } from '@app/models';
import { AlertService, CurrencyService, PortfolioService } from '@app/services';
import { BaseForm } from '@app/shared/components/form/base-form';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import {BaseDialog} from "@app/shared/components/dialog/base-dialog";

@Component({
    templateUrl: 'add-edit.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AddEditComponent extends BaseDialog implements OnInit {
    private readonly portfolioService = inject(PortfolioService);
    private readonly currencyService = inject(CurrencyService);

    public id: WritableSignal<number | null> = signal<number | null>(null);

    public currencies: Currency[];

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
        this.$submitted.set(true);

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.$saving.set(true);
        try {
            if (this.id() === null) {
                this.createPortfolio();
            } else {
                this.updatePortfolio();
            }
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.$saving.set(false);
        }
    }

    private async createPortfolio(): Promise<void> {
        await this.portfolioService.createPortfolio(this.form.value);

        this.alertService.success('Group added successfully', { keepAfterRouteChange: true });
        this.activeModal.dismiss();
        this.portfolioService.notify();
    }

    private async updatePortfolio(): Promise<void> {
        const id = this.id();
        if (id === null) {
            return;
        }

        await this.portfolioService.updatePortfolio(id, this.form.value);

        this.alertService.success('Update successful', { keepAfterRouteChange: true });
        this.activeModal.dismiss();
        this.portfolioService.notify();
    }
}
