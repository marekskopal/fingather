import {ChangeDetectionStrategy, Component, computed, inject, input, OnInit, output, signal} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {ImportDataFile, ImportPrepare} from '@app/models';
import {ImportService, PortfolioService} from "@app/services";
import {ImportFileStatus} from "@app/shared/components/import/types/import-file-status";
import {FileSizePipe} from "@app/shared/pipes/file-size.pipe";
import {TranslateModule} from "@ngx-translate/core";
import {NgxFileDropEntry} from "ngx-file-drop";

@Component({
    templateUrl: 'import-file.component.html',
    selector: 'fingather-import-file',
    standalone: true,
    imports: [
        MatIcon,
        FileSizePipe,
        TranslateModule
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ImportFileComponent implements OnInit {
    private readonly importService = inject(ImportService);
    private readonly portfolioService = inject(PortfolioService);

    public readonly $importId = input.required<number | null>({
        'alias': 'importId',
    });
    public readonly $droppedFile = input.required<NgxFileDropEntry>({
        'alias': 'droppedFile',
    });
    public readonly onUploadFinish$ =  output<ImportPrepare>({
        'alias': 'onUploadFinish',
    });

    protected readonly $fileName = computed<string>(() => this.$droppedFile().fileEntry.name);
    protected readonly $fileSize = signal<number>(0);
    protected readonly $status = signal<ImportFileStatus>(ImportFileStatus.New);
    protected readonly $processed = signal<number>(0);

    private loadingIntervalId: number | undefined = undefined;
    private loadingStartTime: number = 0;

    private readonly fileReader = new FileReader();

    public ngOnInit(): void {
        this.fileReader.onload = (): void => {
            this.createImportPrepare({
                fileName: this.$droppedFile().fileEntry.name,
                contents: this.fileReader.result as string
            });
        };

        this.fileReader.onloadstart = (event: ProgressEvent): void => {
            this.$fileSize.set(event.total as number);
            this.$status.set(ImportFileStatus.Uploading);
        }

        this.fileReader.onloadend = (): void => {
            this.$status.set(ImportFileStatus.Processing);
        }

        this.fileReader.onprogress = (): void => {
            this.startLoading();
        }

        const fileEntry = this.$droppedFile().fileEntry as FileSystemFileEntry;
        fileEntry.file((file: File) => {
            this.fileReader.readAsDataURL(file);
        });
    }

    private async createImportPrepare(importDataFile: ImportDataFile): Promise<void> {
        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const importPrepare = await this.importService.createImportPrepare(
            {
                importId: this.$importId(),
                importDataFile: importDataFile
            },
            portfolio.id
        );

        this.finishLoading();

        this.onUploadFinish$.emit(importPrepare);
    }

    private startLoading(): void {
        this.loadingStartTime = Date.now();

        this.loadingIntervalId = setInterval(() => {
            const elapsedTime = Date.now() - this.loadingStartTime;
            const nextProcessed =  Math.round(Math.atan(elapsedTime / 3e3) / (Math.PI / 2) * 100);

            this.$processed.set(nextProcessed);
        }, 100);
    }

    private finishLoading(): void {
        clearInterval(this.loadingIntervalId);
        this.$processed.set(100);
        this.$status.set(ImportFileStatus.Uploaded);
    }

    protected readonly ImportFileStatus = ImportFileStatus;
}
