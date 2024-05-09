import {
    Component, OnInit, signal, WritableSignal
} from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { Currency } from '@app/models';
import { UserRoleEnum } from '@app/models/enums/user-role-enum';
import { AlertService, CurrencyService, UserService } from '@app/services';
import { BaseForm } from '@app/shared/components/form/base-form';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({ templateUrl: 'add-edit.component.html' })
export class AddEditComponent extends BaseForm implements OnInit {
    public id: WritableSignal<number | null> = signal<number | null>(null);

    public currencies: Currency[];
    public roles = [
        { name: 'User', key: UserRoleEnum.User },
        { name: 'Admin', key: UserRoleEnum.Admin },
    ];

    public constructor(
        private readonly userService: UserService,
        private readonly currencyService: CurrencyService,
        public activeModal: NgbActiveModal,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {
        super(formBuilder, alertService);
    }

    public async ngOnInit(): Promise<void> {
        const emailValidators = [Validators.email];
        const passwordValidators = [Validators.minLength(6)];

        const id = this.id();
        if (id === null) {
            emailValidators.push(Validators.required);
            passwordValidators.push(Validators.required);
        }

        this.form = this.formBuilder.group({
            email: ['', emailValidators],
            name: ['', Validators.required],
            password: ['', passwordValidators],
            defaultCurrencyId: ['', Validators.required],
            role: ['User', Validators.required],
        });

        this.currencies = await this.currencyService.getCurrencies();
        this.f['defaultCurrencyId'].patchValue(this.currencies[0].id);

        if (id !== null) {
            const user = await this.userService.getUser(id);
            this.form.patchValue(user);
        }
    }

    public onSubmit(): void {
        this.submitted = true;

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.loading = true;
        if (this.id() === null) {
            this.createUser();
        } else {
            this.updateUser();
        }
    }

    private async createUser(): Promise<void> {
        try {
            await this.userService.createUser(this.form.value);

            this.alertService.success('User added successfully', { keepAfterRouteChange: true });
            this.activeModal.dismiss();
            this.userService.notify();
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
            this.loading = false;
        }
    }

    private async updateUser(): Promise<void> {
        const id = this.id();
        if (id === null) {
            return;
        }

        try {
            await this.userService.updateUser(id, this.form.value);

            this.alertService.success('Update successful', { keepAfterRouteChange: true });
            this.activeModal.dismiss();
            this.userService.notify();
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
            this.loading = false;
        }
    }
}
