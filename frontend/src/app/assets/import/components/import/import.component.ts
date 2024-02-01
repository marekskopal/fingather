import { Component, OnInit } from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { Broker } from '@app/models';
import {
    AlertService, BrokerService, ImportDataService, PortfolioService
} from '@app/services';
import { BaseForm } from '@app/shared/components/form/base-form';
import { first } from 'rxjs/operators';

@Component({ templateUrl: 'import.component.html' })
export class ImportComponent extends BaseForm implements OnInit {
    public brokerId: string;
    public brokers: Broker[];

    public constructor(
        private readonly router: Router,
        private readonly brokerService: BrokerService,
        private readonly importDataService: ImportDataService,
        private readonly portfolioService: PortfolioService,
        private route: ActivatedRoute,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {
        super(formBuilder, alertService);
    }

    public async ngOnInit(): Promise<void> {
        const portfolio = await this.portfolioService.getCurrentPortfolio();

        this.brokerService.getBrokers(portfolio.id)
            .pipe(first())
            .subscribe((brokers) => this.brokers = brokers);

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

        if (event.target.files && event.target.files.length) {
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
                error: (error) => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }
}
