import {
    ChangeDetectionStrategy,
    Component, inject, OnInit
} from '@angular/core';
import { Validators } from '@angular/forms';
import {ActivatedRoute, Router} from "@angular/router";
import { Currency } from '@app/models';
import { CurrencyService, PortfolioService } from '@app/services';
import {BaseAddEditForm} from "@app/shared/components/form/base-add-edit-form";

@Component({
    templateUrl: 'add-edit-portfolio.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AddEditPortfolioComponent extends BaseAddEditForm implements OnInit {
    private readonly portfolioService = inject(PortfolioService);
    private readonly currencyService = inject(CurrencyService);
    private readonly route = inject(ActivatedRoute);
    private readonly router = inject(Router);

    protected currencies: Currency[];

    public async ngOnInit(): Promise<void> {
        this.$loading.set(true);

        if (this.route.snapshot.params['id'] !== undefined) {
            this.$id.set(this.route.snapshot.params['id']);
        }

        this.form = this.formBuilder.group({
            name: ['My Portfolio', Validators.required],
            currencyId: ['', Validators.required],
            isDefault: [false, Validators.required],
        });

        this.currencies = await this.currencyService.getCurrencies();
        this.f['currencyId'].patchValue(this.currencies[0].id);

        const id = this.$id();
        if (id !== null) {
            const portfolio = await this.portfolioService.getPortfolio(id);
            console.log(portfolio);
            this.form.patchValue(portfolio);
        }

        this.$loading.set(false);
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
            if (this.$id() === null) {
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
        this.portfolioService.notify();
        this.router.navigate(['../'], { relativeTo: this.route });
    }

    private async updatePortfolio(): Promise<void> {
        const id = this.$id();
        if (id === null) {
            return;
        }

        await this.portfolioService.updatePortfolio(id, this.form.value);

        this.alertService.success('Update successful', { keepAfterRouteChange: true });
        this.portfolioService.notify();
        this.router.navigate(['../'], { relativeTo: this.route });
    }
}
