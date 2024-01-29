import {Component, Input} from '@angular/core';
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

    constructor(
        private activeModal: NgbActiveModal
    ) { }

    public decline() {
        this.activeModal.close(false);
    }

    public accept() {
        this.activeModal.close(true);
    }

    public dismiss() {
        this.activeModal.dismiss(false);
    }
}
