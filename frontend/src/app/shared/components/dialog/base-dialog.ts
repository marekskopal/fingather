import {inject} from "@angular/core";
import { BaseForm } from '@app/shared/components/form/base-form';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

export abstract class BaseDialog extends BaseForm {
    public readonly activeModal = inject(NgbActiveModal);
}
