import {
    ChangeDetectionStrategy, Component, inject, signal
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import {TranslateModule} from "@ngx-translate/core";

@Component({
    selector: 'fingather-confirm-dialog',
    templateUrl: 'confirm-dialog.component.html',
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        MatIcon,
        TranslateModule
    ]
})
export class ConfirmDialogComponent {
    private readonly activeModal = inject(NgbActiveModal);

    public $title = signal<string>('');
    public $message = signal<string>('');
    public $buttonOkText = signal<string>('OK');
    public $buttonCancelText = signal<string>('Cancel');

    public decline(): void {
        this.activeModal.close(false);
    }

    public accept(): void {
        this.activeModal.close(true);
    }

    public dismiss(): void {
        this.activeModal.dismiss(false);
    }
}
