import {
    ChangeDetectionStrategy,
    Component, input, InputSignal, OnInit
} from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { ImportPrepare, ImportStart } from '@app/models';
import { ImportMapping } from '@app/models/import-mapping';
import {
    AlertService, ImportService
} from '@app/services';
import { BaseForm } from '@app/shared/components/form/base-form';

@Component({
    templateUrl: 'import-prepare.component.html',
    selector: 'fingather-import-prepare',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ImportPrepareComponent extends BaseForm implements OnInit {
    public importPrepare: InputSignal<ImportPrepare> = input.required<ImportPrepare>();
    public onImportFinish: InputSignal<(() => void) | null> = input<((() => void) | null)>(null);

    public constructor(
        private readonly router: Router,
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
        for (const importPrepareTicker of this.importPrepare().multipleFoundTickers) {
            controlsConfig[`${importPrepareTicker.brokerId}-${importPrepareTicker.ticker}`] = [
                importPrepareTicker.tickers[0].id, Validators.required
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

    private async createImport(): Promise<void> {
        const importStart: ImportStart = {
            importId: this.importPrepare().importId,
            importMappings: [],
        };
        // eslint-disable-next-line
        for (const property in this.form.value) {
            const [brokerId, importTicker] = property.split('-');

            const importMapping: ImportMapping = {
                brokerId: parseInt(brokerId, 10),
                importTicker,
                tickerId: this.form.value[property],
            };

            importStart.importMappings.push(importMapping);
        }

        try {
            await this.importDataService.createImportStart(importStart);

            this.alertService.success('Transactions was imported successfully', { keepAfterRouteChange: true });

            const onImportFinish = this.onImportFinish();
            if (onImportFinish !== null) {
                onImportFinish();
            } else {
                this.router.navigate(['../'], { relativeTo: this.route });
            }
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
            this.loading = false;
        }
    }
}
