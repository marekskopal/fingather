import { Component, OnInit } from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { Broker, ImportDataFile, ImportPrepare } from '@app/models';
import {
    AlertService, BrokerService, ImportService, PortfolioService
} from '@app/services';
import { BaseForm } from '@app/shared/components/form/base-form';
import { NgxFileDropEntry } from 'ngx-file-drop';
import { first } from 'rxjs/operators';

@Component({ templateUrl: 'import.component.html' })
export class ImportComponent extends BaseForm implements OnInit {
    public brokerId: string;
    public brokers: Broker[];
    public importPrepare: ImportPrepare | null = null;
    public droppedFiles: NgxFileDropEntry[] = [];

    public constructor(
        private readonly brokerService: BrokerService,
        private readonly importService: ImportService,
        private readonly portfolioService: PortfolioService,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {
        super(formBuilder, alertService);
    }

    public ngOnInit(): void {
        this.form = this.formBuilder.group({
            importDataFiles: [null, Validators.required],
        });
    }

    public async onSubmit(): Promise<void> {
        this.submitted = true;

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.loading = true;

        this.createImport(portfolio.id);
    }

    public onFileDropped(files: NgxFileDropEntry[]): void {
        this.droppedFiles = this.droppedFiles.concat(files);

        const filesContents: ImportDataFile[] = [];

        for (const droppedFile of this.droppedFiles) {
            const reader = new FileReader();
            reader.onload = (): void => {
                filesContents.push({
                    fileName: droppedFile.fileEntry.name,
                    contents: reader.result as string
                });
                this.form.patchValue({
                    importDataFiles: filesContents
                });
            };

            if (
                !droppedFile.fileEntry.isFile || (
                    !droppedFile.fileEntry.name.endsWith('.csv')
                    && !droppedFile.fileEntry.name.endsWith('.xlsx')
                )
            ) {
                continue;
            }

            const fileEntry = droppedFile.fileEntry as FileSystemFileEntry;
            fileEntry.file((file: File) => {
                reader.readAsDataURL(file);
            });
        }
    }

    private createImport(portfolioId: number): void {
        this.importService.createImportPrepare(this.form.value, portfolioId)
            .pipe(first())
            .subscribe((importPrepare: ImportPrepare) => {
                this.loading = false;
                this.importPrepare = importPrepare;
            });
    }
}
