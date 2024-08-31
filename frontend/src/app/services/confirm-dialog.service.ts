import {inject, Injectable} from '@angular/core';
import { ConfirmDialogComponent } from '@app/shared/components/confirm-dialog/confirm-dialog.component';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Injectable({ providedIn: 'root' })
export class ConfirmDialogService {
    private modalService = inject(NgbModal);

    public confirm(
        title: string,
        message: string,
        btnOkText: string = 'OK',
        btnCancelText: string = 'Cancel',
    ): Promise<boolean> {
        const modalRef = this.modalService.open(ConfirmDialogComponent, { size: 'sm' });
        modalRef.componentInstance.title.set(title);
        modalRef.componentInstance.message.set(message);
        modalRef.componentInstance.btnOkText.set(btnOkText);
        modalRef.componentInstance.btnCancelText.set(btnCancelText);

        return modalRef.result;
    }
}
