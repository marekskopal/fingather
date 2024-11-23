import {
    ChangeDetectionStrategy,
    Component, input, output, signal,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import { ImportPrepare} from '@app/models';
import {ImportFileComponent} from "@app/shared/components/import/components/import-file/import-file.component";
import {ImportPrepareComponent} from "@app/shared/components/import/components/import-prepare/import-prepare.component";
import {DeletedImportFile} from "@app/shared/components/import/types/deleted-import-file";
import { TranslatePipe} from "@ngx-translate/core";
import {NgxFileDropEntry, NgxFileDropModule} from 'ngx-file-drop';
import {v4} from 'uuid';

@Component({
    templateUrl: 'import.component.html',
    selector: 'fingather-import',
    imports: [
        NgxFileDropModule,
        MatIcon,
        ImportFileComponent,
        RouterLink,
        TranslatePipe,
        ImportPrepareComponent,
    ],
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
    protected uuid = v4();

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

    public onFileUploaded(importPrepare: ImportPrepare): void {
        this.$importPrepares.update(() => [...this.$importPrepares(), importPrepare]);
    }

    public onDeleteFile(deletedFile: DeletedImportFile): void {
        this.droppedFiles = this.droppedFiles.filter(
            (file) => file.fileEntry.name !== deletedFile.droppedFile.fileEntry.name,
        );
        this.$importPrepares.update(() => this.$importPrepares().filter(
            (importPrepare) => importPrepare.importFileId !== deletedFile.importFileId),
        );
    }

    protected onImportFinish(): void {
        this.uuid = v4();
        this.onImportFinish$.emit();
    }
}
