import {
    ChangeDetectionStrategy, Component, DestroyRef, inject, OnInit, signal,
} from '@angular/core';
import {MatIcon} from '@angular/material/icon';
import {RouterLink} from '@angular/router';
import {McpApiKey} from '@app/models';
import {McpApiKeyService} from '@app/services/mcp-api-key.service';
import {DeleteButtonComponent} from '@app/shared/components/delete-button/delete-button.component';
import {ScrollShadowDirective} from '@marekskopal/ng-scroll-shadow';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    templateUrl: 'mcp-api-keys.component.html',
    imports: [
        TranslatePipe,
        MatIcon,
        RouterLink,
        DeleteButtonComponent,
        ScrollShadowDirective,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class McpApiKeysComponent implements OnInit {
    private readonly mcpApiKeyService = inject(McpApiKeyService);
    private readonly destroyRef = inject(DestroyRef);

    public readonly mcpApiKeys = signal<McpApiKey[] | null>(null);

    public ngOnInit(): void {
        this.refreshMcpApiKeys();

        this.mcpApiKeyService.subscribe(() => {
            this.refreshMcpApiKeys();
        }, this.destroyRef);
    }

    private async refreshMcpApiKeys(): Promise<void> {
        this.mcpApiKeys.set(null);
        const keys = await this.mcpApiKeyService.getMcpApiKeys();
        this.mcpApiKeys.set(keys);
    }

    protected async copyApiKey(id: number): Promise<void> {
        const apiKey = await this.mcpApiKeyService.getFullApiKey(id);
        await navigator.clipboard.writeText(apiKey);
    }

    protected async deleteMcpApiKey(id: number): Promise<void> {
        await this.mcpApiKeyService.deleteMcpApiKey(id);

        this.mcpApiKeys.update((keys) => (keys !== null
            ? keys.filter((x) => x.id !== id)
            : null));
    }
}
