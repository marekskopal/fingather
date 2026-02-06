import {
    ChangeDetectionStrategy, Component, inject, input, output, signal,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {MatProgressSpinner} from "@angular/material/progress-spinner";
import { AlertService } from '@app/services';
import { ConfirmDialogService } from '@app/services/confirm-dialog.service';
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    selector: 'fingather-delete-button',
    templateUrl: 'delete-button.component.html',
    imports: [
        MatIcon,
        TranslatePipe,
        MatProgressSpinner,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DeleteButtonComponent {
    private readonly confirmDialogService = inject(ConfirmDialogService);
    private readonly alertService = inject(AlertService);

    public readonly confirm = output();

    public readonly title = input.required<string>();
    public readonly message = input.required<string>();
    public readonly showText = input<boolean>(false);
    public readonly text = input<string | null>(null);
    public readonly showAsLink = input<boolean>(false);

    protected readonly isDeleting = signal<boolean>(false);

    protected async delete(): Promise<void> {
        this.isDeleting.set(true);

        try {
            const confirmed = await this.confirmDialogService.confirm(
                this.title(),
                this.message(),
            );
            if (!confirmed) {
                this.isDeleting.set(false);
                return;
            }

            await this.confirm.emit();
        } catch (error) {
            // eslint-disable-next-line no-console
            console.error(error);
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.isDeleting.set(false);
        }
    }
}
