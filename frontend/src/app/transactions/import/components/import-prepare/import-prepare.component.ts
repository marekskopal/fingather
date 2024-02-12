import { Component, Input, OnInit } from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { ImportPrepare, ImportStart } from '@app/models';
import { ImportMapping } from '@app/models/import-mapping';
import {
    AlertService, BrokerService, ImportService
} from '@app/services';
import { BaseForm } from '@app/shared/components/form/base-form';
import { first } from 'rxjs/operators';

@Component({
    templateUrl: 'import-prepare.component.html',
    selector: 'fingather-import-import-prepare',
})
export class ImportPrepareComponent extends BaseForm implements OnInit {
    @Input() public importPrepare: ImportPrepare;

    public constructor(
        private readonly router: Router,
        private readonly brokerService: BrokerService,
        private readonly importDataService: ImportService,
        private route: ActivatedRoute,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {
        super(formBuilder, alertService);
    }

    public ngOnInit(): void {
        const controlsConfig: {
            [key: string]: any;
        } = {};
        for (const importPrepareTicker of this.importPrepare.multipleFoundTickers) {
            controlsConfig[importPrepareTicker.ticker] = [
                importPrepareTicker.tickers[0].market.id, Validators.required
            ];
        }

        this.form = this.formBuilder.group(controlsConfig);
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
        const importStart = new ImportStart();
        importStart.importId = this.importPrepare.importId;
        importStart.importMappings = [];
        // eslint-disable-next-line
        for (const property in this.form.value) {
            const importMapping = new ImportMapping();
            importMapping.importTicker = property;
            importMapping.tickerId = this.form.value[property];

            importStart.importMappings.push(importMapping);
        }

        this.importDataService.createImportStart(importStart)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Transactions was imported successfully', { keepAfterRouteChange: true });
                    this.router.navigate(['../'], { relativeTo: this.route });
                },
                error: (error) => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }
}
