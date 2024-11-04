import {
    ChangeDetectionStrategy,
    Component, inject, OnInit,
} from '@angular/core';
import {ReactiveFormsModule, Validators} from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import { Router, RouterLink} from "@angular/router";
import { UserRoleEnum } from '@app/models/enums/user-role-enum';
import {CurrencyService, CurrentUserService, UserService} from '@app/services';
import {BaseAddEditForm} from "@app/shared/components/form/base-add-edit-form";
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import {SelectComponent} from "@app/shared/components/select/select.component";
import {SelectItem} from "@app/shared/types/select-item";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: 'add-edit-user.component.html',
    standalone: true,
    imports: [
        TranslatePipe,
        PortfolioSelectorComponent,
        RouterLink,
        MatIcon,
        ReactiveFormsModule,
        InputValidatorComponent,
        SelectComponent,
        SaveButtonComponent
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AddEditUserComponent extends BaseAddEditForm implements OnInit {
    private readonly userService = inject(UserService);
    private readonly currencyService = inject(CurrencyService);
    private readonly currentUserService = inject(CurrentUserService);
    private readonly router = inject(Router);

    protected currencies: SelectItem<number, string>[] = [];
    protected roles: SelectItem<UserRoleEnum, UserRoleEnum>[] = [
        {key: UserRoleEnum.User, label: UserRoleEnum.User},
        {key: UserRoleEnum.Admin, label: UserRoleEnum.Admin}
    ];

    public async ngOnInit(): Promise<void> {
        this.$loading.set(true);

        const currentUser = await this.currentUserService.getCurrentUser();
        if (currentUser.role !== UserRoleEnum.Admin) {
            this.router.navigate(['/']);
        }

        this.initializeIdFromRoute();

        const emailValidators = [Validators.email];
        const passwordValidators = [Validators.minLength(6)];

        const id = this.$id();
        if (id === null) {
            emailValidators.push(Validators.required);
            passwordValidators.push(Validators.required);
        }

        const currencies = await this.currencyService.getCurrencies();
        this.currencies = currencies.map((currency) => {
            return {
                key: currency.id,
                label: currency.code,
            }
        });

        this.form = this.formBuilder.group({
            email: ['', emailValidators],
            name: ['', Validators.required],
            password: ['', passwordValidators],
            defaultCurrencyId: [this.currencies[0].key, Validators.required],
            role: [UserRoleEnum.User, Validators.required],
        });

        if (id !== null) {
            const user = await this.userService.getUser(id);
            this.form.patchValue(user);
        }

        this.$loading.set(false);
    }

    public onSubmit(): void {
        this.$submitted.set(true);

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.$saving.set(true);
        try {
            if (this.$id() === null) {
                this.createUser();
            } else {
                this.updateUser();
            }
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.$saving.set(false);
        }
    }

    private async createUser(): Promise<void> {
        await this.userService.createUser(this.form.value);

        this.alertService.success('User added successfully', { keepAfterRouteChange: true });
        this.userService.notify();
        this.router.navigate(['../'], { relativeTo: this.route });
    }

    private async updateUser(): Promise<void> {
        const id = this.$id();
        if (id === null) {
            return;
        }

        await this.userService.updateUser(id, this.form.value);

        this.alertService.success('Update successful', { keepAfterRouteChange: true });
        this.userService.notify();
        this.router.navigate(['../'], { relativeTo: this.route });
    }
}
