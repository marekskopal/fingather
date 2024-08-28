import {
    ChangeDetectionStrategy,
    Component, input, output, signal
} from '@angular/core';
import { ImportPrepare} from '@app/models';
import { NgxFileDropEntry } from 'ngx-file-drop';

@Component({
    templateUrl: 'import.component.html',
    selector: 'fingather-import',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ImportComponent {
    public $showCancel = input<boolean>(true, {
        alias: 'showCancel',
    });
    public onImportFinish$ = output<void>({
        'alias': 'onImportFinish',
    });

    protected $importPrepares = signal<ImportPrepare[]>([]);
    protected droppedFiles: NgxFileDropEntry[] = [];
    protected $importId = signal<number | null>(null);

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

    public async onFileUploaded(importPrepare: ImportPrepare): Promise<void> {
        this.$importId.set(importPrepare.importId);
        this.$importPrepares.update(() => [...this.$importPrepares(), importPrepare]);
    }
}
