import {
    ChangeDetectionStrategy,
    Component, inject, OnInit,
} from '@angular/core';
import {ReactiveFormsModule, Validators} from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import { Router, RouterLink} from "@angular/router";
import {ApiKeyTypeEnum} from "@app/models/enums/api-key-type-enum";
import {ApiKeyService, PortfolioService} from '@app/services';
import {BaseAddEditForm} from "@app/shared/components/form/base-add-edit-form";
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import {SelectComponent} from "@app/shared/components/select/select.component";
import {SelectItem} from "@app/shared/types/select-item";
import {TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: 'add-edit-api-key.component.html',
    imports: [
        ReactiveFormsModule,
        RouterLink,
        MatIcon,
        InputValidatorComponent,
        SaveButtonComponent,
        PortfolioSelectorComponent,
        TranslatePipe,
        SelectComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AddEditApiKeyComponent extends BaseAddEditForm implements OnInit {
    private readonly apiKeyService = inject(ApiKeyService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly router = inject(Router);

    protected readonly ApiKeyTypeEnum = ApiKeyTypeEnum;

    protected types: SelectItem<ApiKeyTypeEnum, ApiKeyTypeEnum>[] = [
        {key: ApiKeyTypeEnum.Trading212, label: ApiKeyTypeEnum.Trading212},
        {key: ApiKeyTypeEnum.Etoro, label: ApiKeyTypeEnum.Etoro},
    ];

    public async ngOnInit(): Promise<void> {
        this.loading.set(true);

        this.initializeIdFromRoute();

        this.form = this.formBuilder.group({
            type: [ApiKeyTypeEnum.Trading212, Validators.required],
            apiKey: ['', Validators.required],
            userKey: [''],
        });

        const id = this.id();
        if (id !== null) {
            const apiKey = await this.apiKeyService.getApiKey(id);
            this.form.patchValue(apiKey);
        }

        this.loading.set(false);
    }

    public onSubmit(): void {
        this.submitted.set(true);

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.saving.set(true);
        try {
            if (this.id() === null) {
                this.createApiKey();
            } else {
                this.updateApiKey();
            }
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.saving.set(false);
        }
    }

    private async createApiKey(): Promise<void> {
        const portfolio = await this.portfolioService.getCurrentPortfolio();
        await this.apiKeyService.createApiKey(this.form.value, portfolio.id);

        this.alertService.success('API key added successfully', { keepAfterRouteChange: true });
        this.apiKeyService.notify();
        this.router.navigate([this.routerBackLink()], { relativeTo: this.route });
    }

    private async updateApiKey(): Promise<void> {
        const id = this.id();
        if (id === null) {
            return;
        }

        await this.apiKeyService.updateApiKey(id, this.form.value);

        this.alertService.success('Update successful', { keepAfterRouteChange: true });
        this.apiKeyService.notify();
        this.router.navigate([this.routerBackLink()], { relativeTo: this.route });
    }
}
