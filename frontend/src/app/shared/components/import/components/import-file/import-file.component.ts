import {ChangeDetectionStrategy, Component, computed, input, OnInit, output, signal} from '@angular/core';
import {ImportDataFile} from '@app/models';
import {NgxFileDropEntry} from "ngx-file-drop";
import {ImportFileStatus} from "@app/shared/components/import/types/import-file-status";

@Component({
    templateUrl: 'import-file.component.html',
    selector: 'fingather-import-file',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ImportFileComponent implements OnInit {
    public readonly $droppedFile = input.required<NgxFileDropEntry>({
        'alias': 'droppedFile',
    });
    public readonly onUploadFinish$ =  output<ImportDataFile>({
        'alias': 'onUploadFinish',
    });

    protected readonly $fileName = computed<string>(() => this.$droppedFile().fileEntry.name);
    protected readonly $fileSize = signal<number>(0);
    protected readonly $status = signal<ImportFileStatus>(ImportFileStatus.New);
    protected readonly $processed = signal<number>(0);

    private readonly fileReader = new FileReader();

    public ngOnInit(): void {
        this.fileReader.onload = (): void => {
            this.onUploadFinish$.emit({
                fileName: this.$droppedFile().fileEntry.name,
                contents: this.fileReader.result as string
            });
        };

        this.fileReader.onloadstart = (event: ProgressEvent): void => {
            this.$fileSize.set(event.total as number);
            this.$status.set(ImportFileStatus.Uploading);
        }

        this.fileReader.onloadend = (): void => {
            this.$status.set(ImportFileStatus.Uploaded);
        }

        this.fileReader.onprogress = (event: ProgressEvent): void => {
            this.$processed.set(event.loaded / event.total * 100);
        }

        const fileEntry = this.$droppedFile().fileEntry as FileSystemFileEntry;
        fileEntry.file((file: File) => {
            this.fileReader.readAsDataURL(file);
        });
    }

    protected readonly ImportFileStatus = ImportFileStatus;
}
