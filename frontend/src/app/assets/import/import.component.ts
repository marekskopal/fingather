import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { first } from 'rxjs/operators';

import {Broker} from "@app/models";
import {AlertService, BrokerService, ImportDataService} from "@app/services";
import {BaseForm} from "@app/shared/components/form/base-form";

@Component({ templateUrl: 'import.component.html' })
export class ImportComponent extends BaseForm implements OnInit {
    public brokerId: string;
    public brokers: Broker[];

    public constructor(
        private route: ActivatedRoute,
        private router: Router,
        private brokerService: BrokerService,
        private importDataService: ImportDataService,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {
        super(formBuilder, alertService)
    }

    public ngOnInit(): void {
        this.brokerService.getBrokers()
            .pipe(first())
            .subscribe(brokers => this.brokers = brokers);

        this.form = this.formBuilder.group({
            brokerId: [this.brokerId, Validators.required],
            data: [null, Validators.required],
        });
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

        this.createImport();
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public onFileChange(event: any): void {
        const reader = new FileReader();

        if(event.target.files && event.target.files.length) {
            const [file] = event.target.files;
            reader.readAsDataURL(file);

            reader.onload = (): void => {
                this.form.patchValue({
                    data: reader.result
                });
            };
        }
    }

    private createImport(): void {
        this.importDataService.createImportData(this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Asset added successfully', { keepAfterRouteChange: true });
                    this.router.navigate(['../'], { relativeTo: this.route });
                },
                error: error => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }
}
