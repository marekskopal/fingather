import { UntypedFormBuilder } from '@angular/forms';
import { AlertService } from '@app/services';
import { BaseForm } from '@app/shared/components/form/base-form';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import {inject} from "@angular/core";

export abstract class BaseDialog extends BaseForm {
    public readonly activeModal = inject(NgbActiveModal);
}
