import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { first } from 'rxjs/operators';

import {Broker} from "@app/models";
import {AlertService, BrokerService, ImportDataService, TransactionService} from "@app/services";

@Component({ templateUrl: 'import.component.html' })
export class ImportComponent implements OnInit {
    public form: UntypedFormGroup;
    public brokerId: string;
    public loading = false;
    public submitted = false;
    public brokers: Broker[];

    public constructor(
        private formBuilder: UntypedFormBuilder,
        private route: ActivatedRoute,
        private router: Router,
        private transactionService: TransactionService,
        private alertService: AlertService,
        private brokerService: BrokerService,
        private importDataService: ImportDataService,
    ) {}

    public ngOnInit(): void {
        this.brokerService.findAll()
            .pipe(first())
            .subscribe(brokers => this.brokers = brokers);

        this.form = this.formBuilder.group({
            brokerId: [this.brokerId, Validators.required],
            data: [null, Validators.required],
        });
    }

    // convenience getter for easy access to form fields
    public get f() { return this.form.controls; }

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

            reader.onload = () => {
                this.form.patchValue({
                    data: reader.result
                });
            };
        }
    }

    private createImport() {
        this.importDataService.create(this.form.value)
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
