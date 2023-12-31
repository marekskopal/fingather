import { UntypedFormBuilder, UntypedFormGroup } from "@angular/forms";
import { AlertService } from "@app/services";
import { NgbActiveModal } from "@ng-bootstrap/ng-bootstrap";

export abstract class ABaseDialog
{
    public form: UntypedFormGroup;
    public isAddMode: boolean;
    public loading = false;
    public submitted = false;

    public constructor(
        protected formBuilder: UntypedFormBuilder,
        public activeModal: NgbActiveModal,
        protected alertService: AlertService,
    ) {}

    public get f() {
        return this.form.controls;
    }

    public abstract onSubmit(): void;
}
