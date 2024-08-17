import {
    ChangeDetectionStrategy,
    Component, inject, input, InputSignal, OnInit
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
    private readonly router = inject(Router);
    private readonly importDataService = inject(ImportService);
    private route = inject(ActivatedRoute);

    public importPrepare: InputSignal<ImportPrepare> = input.required<ImportPrepare>();
    public onImportFinish: InputSignal<(() => void) | null> = input<((() => void) | null)>(null);

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
        this.$submitted.set(true);

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.$saving.set(true);
        try {
            this.createImport();
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.$saving.set(false);
        }
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

        await this.importDataService.createImportStart(importStart);

        this.alertService.success('Transactions was imported successfully', { keepAfterRouteChange: true });

        const onImportFinish = this.onImportFinish();
        if (onImportFinish !== null) {
            onImportFinish();
        } else {
            this.router.navigate(['../'], { relativeTo: this.route });
        }
    }
}
