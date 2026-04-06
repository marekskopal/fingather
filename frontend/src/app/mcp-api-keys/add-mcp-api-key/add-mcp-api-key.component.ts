import {
    ChangeDetectionStrategy,
    Component, inject,
} from '@angular/core';
import {ReactiveFormsModule, Validators} from '@angular/forms';
import {MatIcon} from '@angular/material/icon';
import {Router, RouterLink} from '@angular/router';
import {McpApiKeyService} from '@app/services/mcp-api-key.service';
import {BaseForm} from '@app/shared/components/form/base-form';
import {InputValidatorComponent} from '@app/shared/components/input-validator/input-validator.component';
import {SaveButtonComponent} from '@app/shared/components/save-button/save-button.component';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    templateUrl: 'add-mcp-api-key.component.html',
    imports: [
        ReactiveFormsModule,
        RouterLink,
        MatIcon,
        InputValidatorComponent,
        SaveButtonComponent,
        TranslatePipe,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AddMcpApiKeyComponent extends BaseForm {
    private readonly mcpApiKeyService = inject(McpApiKeyService);
    private readonly router = inject(Router);

    public constructor() {
        super();
        this.form = this.formBuilder.group({
            name: ['', Validators.required],
        });
    }

    public onSubmit(): void {
        this.submitted.set(true);
        this.alertService.clear();

        if (this.form.invalid) {
            return;
        }

        this.saving.set(true);
        this.createMcpApiKey().finally(() => this.saving.set(false));
    }

    private async createMcpApiKey(): Promise<void> {
        try {
            await this.mcpApiKeyService.createMcpApiKey(this.form.value.name);
            this.mcpApiKeyService.notify();
            await this.router.navigate(['/settings/mcp-api-keys']);
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        }
    }
}
