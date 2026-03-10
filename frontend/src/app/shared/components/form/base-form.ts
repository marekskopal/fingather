import { inject, signal} from '@angular/core';
import { AbstractControl, FormBuilder, FormGroup } from '@angular/forms';
import { AlertService } from '@app/services';

export abstract class BaseForm {
    protected form!: FormGroup;
    protected readonly loading = signal<boolean>(false);
    protected readonly saving = signal<boolean>(false);
    protected readonly submitted = signal<boolean>(false);

    protected readonly formBuilder = inject(FormBuilder);
    protected readonly alertService = inject(AlertService);

    public get f(): { [key: string]: AbstractControl } {
        return this.form.controls;
    }

    public abstract onSubmit(): void;
}
