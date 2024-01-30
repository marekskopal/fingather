import { UntypedFormBuilder} from '@angular/forms';
import { AlertService } from '@app/services';
import { BaseForm } from '@app/shared/components/form/base-form';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

export abstract class BaseDialog extends BaseForm
{
    public isAddMode: boolean;

    public constructor(
        public activeModal: NgbActiveModal,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {
        super(formBuilder, alertService)
    }
}
