import {
    ChangeDetectionStrategy,
    Component,
    computed,
    inject,
    Injector,
    input,
    OnInit,
    output,
    signal
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {ImportDataFile, ImportPrepare} from '@app/models';
import {ImportService, PortfolioService} from "@app/services";
import {FakeLoadingService} from "@app/services/fake-loading.service";
import {DeletedImportFile} from "@app/shared/components/import/types/deleted-import-file";
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
    private readonly injector = inject(Injector);
    private readonly fakeLoadingService = Injector
        .create({ providers: [FakeLoadingService], parent: this.injector })
        .get(FakeLoadingService);

    public readonly $uuid = input.required<string>({
        'alias': 'uuid',
    });
    public readonly $droppedFile = input.required<NgxFileDropEntry>({
        'alias': 'droppedFile',
    });
    public readonly onUploadFinish$ =  output<ImportPrepare>({
        'alias': 'onUploadFinish',
    });
    public readonly onDeleteFile$ =  output<DeletedImportFile>({
        'alias': 'onDeleteFile',
    });

    protected readonly $fileName = computed<string>(() => this.$droppedFile().fileEntry.name);
    protected readonly $fileSize = signal<number>(0);
    protected readonly $status = signal<ImportFileStatus>(ImportFileStatus.New);
    protected readonly $processed = this.fakeLoadingService.$processed;

    private readonly $importFileId = signal<number | null>(null);

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
            this.fakeLoadingService.startLoading();
        }

        const fileEntry = this.$droppedFile().fileEntry as FileSystemFileEntry;
        fileEntry.file((file: File) => {
            this.fileReader.readAsDataURL(file);
        });
    }

    protected async deleteFile(): Promise<void> {
        const importFileId = this.$importFileId();
        if (importFileId === null) {
            return;
        }

        await this.importService.deleteImportFile(importFileId);

        this.onDeleteFile$.emit({
            importFileId: importFileId,
            droppedFile: this.$droppedFile()
        });
    }

    private async createImportPrepare(importDataFile: ImportDataFile): Promise<void> {
        const portfolio = await this.portfolioService.getCurrentPortfolio();

        try {
            const importPrepare = await this.importService.createImportPrepare(
                {
                    uuid: this.$uuid(),
                    importDataFile: importDataFile
                },
                portfolio.id
            );

            this.$importFileId.set(importPrepare.importFileId);

            this.finishLoading();

            this.onUploadFinish$.emit(importPrepare);
        } catch (error) {
            if (error === 'Imported file is not supported.') {
                this.$status.set(ImportFileStatus.NotSupported);
            } else {
                this.$status.set(ImportFileStatus.Error);
            }

            this.fakeLoadingService.finishLoading();
        }
    }

    private finishLoading(): void {
        this.fakeLoadingService.finishLoading();
        this.$status.set(ImportFileStatus.Uploaded);
    }

    protected readonly ImportFileStatus = ImportFileStatus;
}
