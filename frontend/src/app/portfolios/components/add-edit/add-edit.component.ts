import {Component, Input, OnInit, signal, WritableSignal} from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import {AlertService, CurrencyService, PortfolioService} from '@app/services';
import { BaseForm } from '@app/shared/components/form/base-form';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { first } from 'rxjs/operators';
import {Currency} from "@app/models";

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

    public ngOnInit(): void {
        this.form = this.formBuilder.group({
            name: ['My Portfolio', Validators.required],
            currencyId: ['', Validators.required],
            isDefault: [false, Validators.required],
        });

        this.currencyService.getCurrencies()
            .pipe(first())
            .subscribe((currencies: Currency[]) => {
                this.currencies = currencies;
                this.f['defaultCurrencyId'].patchValue(currencies[0].id);
            });

        const id = this.id();
        if (id !== null) {
            this.portfolioService.getPortfolio(id)
                .pipe(first())
                .subscribe((x) => this.form.patchValue(x));
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

    private createPortfolio(): void {
        this.portfolioService.createPortfolio(this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Group added successfully', { keepAfterRouteChange: true });
                    this.activeModal.dismiss();
                    this.portfolioService.notify();
                },
                error: (error) => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }

    private updatePortfolio(): void {
        const id = this.id();
        if (id === null) {
            return;
        }

        this.portfolioService.updatePortfolio(id, this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Update successful', { keepAfterRouteChange: true });
                    this.activeModal.dismiss();
                    this.portfolioService.notify();
                },
                error: (error) => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }
}
