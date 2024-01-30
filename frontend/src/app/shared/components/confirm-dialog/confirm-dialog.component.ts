import { Component, Input } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
    selector: 'fingather-confirm-dialog',
    templateUrl: 'confirm-dialog.component.html'
})
export class ConfirmDialogComponent {
    @Input() public title: string;
    @Input() public message: string;
    @Input() public btnOkText: string;
    @Input() public btnCancelText: string;

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
