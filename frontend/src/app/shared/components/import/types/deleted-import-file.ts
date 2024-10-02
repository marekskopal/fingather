import {NgxFileDropEntry} from "ngx-file-drop";

export interface DeletedImportFile {
    importFileId: number;
    droppedFile: NgxFileDropEntry;
}
