import {Component, Input, OnInit} from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { UntypedFormBuilder, Validators} from '@angular/forms';
import { first } from 'rxjs/operators';

import {UserService, AlertService, CurrencyService} from '@app/services';
import {Currency, UserRoleEnum} from "@app/models";
import {BaseForm} from "@app/shared/components/form/base-form";
import {NgbActiveModal} from "@ng-bootstrap/ng-bootstrap";

@Component({ templateUrl: 'add-edit.component.html' })
export class AddEditComponent extends BaseForm implements OnInit {
    @Input() public id: number;
    public isAddMode: boolean;
    public currencies: Currency[];
    public roles = [
        {name: 'User', key: UserRoleEnum.User},
        {name: 'Admin', key: UserRoleEnum.Admin},
    ]

    public constructor(
        private route: ActivatedRoute,
        private router: Router,
        private userService: UserService,
        private currencyService: CurrencyService,
        public activeModal: NgbActiveModal,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {
        super(formBuilder, alertService);
    }

    public async ngOnInit(): Promise<void> {
        this.isAddMode = !this.id;

        const emailValidators = [Validators.email]
        const passwordValidators = [Validators.minLength(6)];
        if (this.isAddMode) {
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

        this.currencyService.getCurrencies()
            .pipe(first())
            .subscribe((currencies: Currency[]) => {
                this.currencies = currencies;
                this.f['defaultCurrencyId'].patchValue(currencies[0].id);
            });

        if (!this.isAddMode) {
            this.userService.getUser(this.id)
                .pipe(first())
                .subscribe(x => this.form.patchValue(x));
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
        if (this.isAddMode) {
            this.createUser();
        } else {
            this.updateUser();
        }
    }

    private createUser(): void {
        this.userService.createUser(this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('User added successfully', { keepAfterRouteChange: true });
                    this.activeModal.dismiss();
                    this.userService.notify();
                },
                error: error => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }

    private updateUser(): void {
        this.userService.updateUser(this.id, this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Update successful', { keepAfterRouteChange: true });
                    this.activeModal.dismiss();
                    this.userService.notify();
                },
                error: error => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }
}
