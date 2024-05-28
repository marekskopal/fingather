import {
    ChangeDetectionStrategy, Component, signal, WritableSignal
} from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
    selector: 'fingather-confirm-dialog',
    templateUrl: 'confirm-dialog.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ConfirmDialogComponent {
    public title: WritableSignal<string> = signal<string>('');
    public message: WritableSignal<string> = signal<string>('');
    public btnOkText: WritableSignal<string> = signal<string>('OK');
    public btnCancelText: WritableSignal<string> = signal<string>('Cancel');

    public constructor(
        private activeModal: NgbActiveModal
    ) { }

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
