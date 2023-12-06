import { UntypedFormBuilder, UntypedFormGroup } from "@angular/forms";
import { AlertService } from "@app/_services";
import { NgbActiveModal } from "@ng-bootstrap/ng-bootstrap";

export abstract class ABaseDialog
{
    public form: UntypedFormGroup;
    public isAddMode: boolean;
    public loading = false;
    public submitted = false;

    constructor(
        protected formBuilder: UntypedFormBuilder,
        public activeModal: NgbActiveModal,
        protected alertService: AlertService,
    ) {}

    get f() { 
        return this.form.controls;
    }

    abstract onSubmit(): void;
}