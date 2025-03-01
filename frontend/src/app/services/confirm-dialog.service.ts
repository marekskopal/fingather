import {inject, Injectable} from '@angular/core';
import { ConfirmDialogComponent } from '@app/shared/components/confirm-dialog/confirm-dialog.component';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Injectable({ providedIn: 'root' })
export class ConfirmDialogService {
    private modalService = inject(NgbModal);

    public confirm(
        title: string,
        message: string,
        buttonOkText: string | null = null,
        buttonCancelText: string | null = null,
    ): Promise<boolean> {
        const modalRef = this.modalService.open(ConfirmDialogComponent, {
            size: 'md',
            centered: true,
        });
        modalRef.componentInstance.title.set(title);
        modalRef.componentInstance.message.set(message);
        modalRef.componentInstance.buttonOkText.set(buttonOkText);
        modalRef.componentInstance.buttonCancelText.set(buttonCancelText);

        return modalRef.result;
    }
}
