import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit, signal,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import {ApiKey} from '@app/models';
import { PortfolioService } from '@app/services';
import {ApiKeyService} from "@app/services/api-key.service";
import {DeleteButtonComponent} from "@app/shared/components/delete-button/delete-button.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";
import {TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: 'api-keys.component.html',
    imports: [
        TranslatePipe,
        MatIcon,
        RouterLink,
        PortfolioSelectorComponent,
        DeleteButtonComponent,
        ScrollShadowDirective,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ApiKeysComponent implements OnInit {
    private readonly apiKeyService = inject(ApiKeyService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly changeDetectorRef = inject(ChangeDetectorRef);

    public readonly $apiKeys = signal<ApiKey[] | null>(null);

    public ngOnInit(): void {
        this.refreshApiKeys();

        this.apiKeyService.subscribe(() => {
            this.refreshApiKeys();
            this.changeDetectorRef.detectChanges();
        });

        this.portfolioService.subscribe(() => {
            this.refreshApiKeys();
            this.changeDetectorRef.detectChanges();
        });
    }

    protected get apiKeys(): ApiKey[] | null {
        return this.$apiKeys();
    }

    private async refreshApiKeys(): Promise<void> {
        this.$apiKeys.set(null);

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const groups = await this.apiKeyService.getApiKeys(portfolio.id);
        this.$apiKeys.set(groups);
    }

    protected async deleteApiKey(id: number): Promise<void> {
        const group = this.$apiKeys()?.find((x) => x.id === id);
        if (group === undefined) {
            return;
        }

        await this.apiKeyService.deleteApiKey(id);

        this.$apiKeys.update((apiKeys) => (apiKeys !== null
            ? apiKeys.filter((x) => x.id !== id)
            : null));
    }
}
