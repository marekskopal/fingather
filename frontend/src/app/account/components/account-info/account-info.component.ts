import {ChangeDetectionStrategy, Component, inject, OnInit, signal} from '@angular/core';
import {AbstractControl, ReactiveFormsModule, ValidationErrors, Validators} from '@angular/forms';
import {UniqueEmailValidator} from '@app/authentication/validator/UniqueEmailValidator';
import {User} from '@app/models';
import {CurrentUserService} from '@app/services';
import {BaseForm} from '@app/shared/components/form/base-form';
import {InputValidatorComponent} from '@app/shared/components/input-validator/input-validator.component';
import {PortfolioSelectorComponent} from '@app/shared/components/portfolio-selector/portfolio-selector.component';
import {SaveButtonComponent} from '@app/shared/components/save-button/save-button.component';
import {TranslateModule} from '@ngx-translate/core';

@Component({
    templateUrl: 'account-info.component.html',
    standalone: true,
    imports: [
        PortfolioSelectorComponent,
        TranslateModule,
        ReactiveFormsModule,
        InputValidatorComponent,
        SaveButtonComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AccountInfoComponent extends BaseForm implements OnInit {
    private readonly currentUserService = inject(CurrentUserService);
    private readonly uniqueEmailValidator = inject(UniqueEmailValidator);

    protected readonly editing = signal<boolean>(false);
    protected readonly user = signal<User | null>(null);
    private originalEmail: string = '';

    public async ngOnInit(): Promise<void> {
        this.loading.set(true);

        const user = await this.currentUserService.getCurrentUser();
        this.user.set(user);
        this.originalEmail = user.email;

        this.form = this.formBuilder.group({
            name: [user.name, Validators.required],
            email: [
                user.email,
                [Validators.required, Validators.email],
                [this.validateUniqueEmail.bind(this)],
                'blur',
            ],
            password: ['', [Validators.minLength(6)]],
            isEmailNotificationsEnabled: [user.isEmailNotificationsEnabled],
        });

        this.loading.set(false);
    }

    protected onEdit(): void {
        this.editing.set(true);
    }

    protected onCancelEdit(): void {
        const user = this.user();
        if (user !== null) {
            this.form.patchValue({
                name: user.name,
                email: user.email,
                password: '',
                isEmailNotificationsEnabled: user.isEmailNotificationsEnabled,
            });
        }
        this.submitted.set(false);
        this.editing.set(false);
    }

    public onSubmit(): void {
        this.submitted.set(true);

        this.alertService.clear();

        if (this.form.invalid) {
            return;
        }

        this.saving.set(true);
        this.updateCurrentUser();
    }

    private async validateUniqueEmail(control: AbstractControl): Promise<ValidationErrors | null> {
        if (control.value === this.originalEmail) {
            return null;
        }

        return this.uniqueEmailValidator.validate(control);
    }

    private async updateCurrentUser(): Promise<void> {
        try {
            const updatedUser = await this.currentUserService.updateCurrentUser(this.form.value);

            this.user.set(updatedUser);
            this.originalEmail = updatedUser.email;
            this.editing.set(false);
            this.submitted.set(false);
            this.alertService.success('Update successful', {keepAfterRouteChange: true});
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.saving.set(false);
        }
    }
}
