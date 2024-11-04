import {ChangeDetectionStrategy, Component, inject, OnInit} from '@angular/core';
import {ReactiveFormsModule, Validators} from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import {ActivatedRoute, Router, RouterLink} from "@angular/router";
import { AssetService, PortfolioService
} from '@app/services';
import { BaseForm } from "@app/shared/components/form/base-form";
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import {
    TickerSearchSelectorComponent
} from "@app/shared/components/ticker-search-selector/ticker-search-selector.component";
import {NgbHighlight, NgbTypeahead} from "@ng-bootstrap/ng-bootstrap";
import { TranslatePipe} from "@ngx-translate/core";


@Component({
    templateUrl: 'add-asset.component.html',
    standalone: true,
    imports: [
        NgbHighlight,
        PortfolioSelectorComponent,
        RouterLink,
        MatIcon,
        TranslatePipe,
        ReactiveFormsModule,
        NgbTypeahead,
        InputValidatorComponent,
        SaveButtonComponent,
        TickerSearchSelectorComponent
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AddAssetComponent extends BaseForm implements OnInit {
    private readonly assetService = inject(AssetService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly route = inject(ActivatedRoute);
    private readonly router = inject(Router);

    public ngOnInit(): void {
        this.form = this.formBuilder.group({
            tickerId: ['', Validators.required],
        });
    }

    public async onSubmit(): Promise<void> {
        this.$submitted.set(true);

        this.alertService.clear();

        if (this.form.invalid) {
            return;
        }

        const portfolio = await this.portfolioService.getCurrentPortfolio();
        this.$saving.set(true);
        try {
            this.createAsset(portfolio.id);
        } catch (error: unknown) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.$saving.set(false);
        }
    }

    private async createAsset(portfolioId: number): Promise<void> {
        await this.assetService.createAsset(this.form.value, portfolioId);

        this.alertService.success('Asset added successfully', { keepAfterRouteChange: true });
        this.assetService.notify();
        this.router.navigate(['../'], { relativeTo: this.route });
    }
}
