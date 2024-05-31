import { AbstractControl, UntypedFormBuilder, UntypedFormGroup } from '@angular/forms';
import { AlertService } from '@app/services';
import {signal} from "@angular/core";

export abstract class BaseForm {
    protected form: UntypedFormGroup;
    protected readonly $loading = signal<boolean>(false);
    protected readonly $saving = signal<boolean>(false);
    protected readonly $submitted= signal<boolean>(false);

    public constructor(
        protected readonly formBuilder: UntypedFormBuilder,
        protected readonly alertService: AlertService,
    ) {}

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public get f(): { [key: string]: AbstractControl<any, any> } {
        return this.form.controls;
    }

    public abstract onSubmit(): void;
}
