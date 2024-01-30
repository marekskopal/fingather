import { AbstractControl, UntypedFormBuilder, UntypedFormGroup } from '@angular/forms';
import { AlertService } from '@app/services';

export abstract class BaseForm {
    public form: UntypedFormGroup;
    public loading: boolean = false;
    public submitted: boolean = false;

    public constructor(
        protected formBuilder: UntypedFormBuilder,
        protected alertService: AlertService,
    ) {}

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public get f(): { [key: string]: AbstractControl<any, any> } {
        return this.form.controls;
    }

    public abstract onSubmit(): void;
}
