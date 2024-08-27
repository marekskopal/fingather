import {
    ChangeDetectionStrategy,
    Component, inject, input, InputSignal, OnInit, output, signal
} from '@angular/core';
import { Validators } from '@angular/forms';
import {ImportData, ImportDataFile, ImportPrepare} from '@app/models';
import { ImportService, PortfolioService
} from '@app/services';
import { BaseForm } from '@app/shared/components/form/base-form';
import { NgxFileDropEntry } from 'ngx-file-drop';

@Component({
    templateUrl: 'import.component.html',
    selector: 'fingather-import',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ImportComponent {
    private readonly importService = inject(ImportService);
    private readonly portfolioService = inject(PortfolioService);

    public $showCancel = input<boolean>(true, {
        alias: 'showCancel',
    });
    public onImportFinish$ = output<void>({
        'alias': 'onImportFinish',
    });

    protected $importPrepare = signal<ImportPrepare | null>(null);
    protected droppedFiles: NgxFileDropEntry[] = [];
    private importDataFiles: ImportDataFile[] = [];

    public onFileDropped(files: NgxFileDropEntry[]): void {
        for (const droppedFile of files) {
            if (
                !droppedFile.fileEntry.isFile || (
                    !droppedFile.fileEntry.name.endsWith('.csv')
                    && !droppedFile.fileEntry.name.endsWith('.xlsx')
                )
            ) {
                continue;
            }

            this.droppedFiles.push(droppedFile);
        }
    }

    public async onFileUploaded(importDataFile: ImportDataFile): Promise<void> {
        this.importDataFiles.push(importDataFile);

        if (this.droppedFiles.length === this.importDataFiles.length) {
            const portfolio = await this.portfolioService.getCurrentPortfolio();
            this.createImport(portfolio.id);
        }
    }

    private async createImport(portfolioId: number): Promise<void> {
        const importPrepare = await this.importService.createImportPrepare(
            {
                importDataFiles: this.importDataFiles
            },
            portfolioId
        );
        this.$importPrepare.set(importPrepare);
    }
}
