import {ImportDataFile} from "@app/models/import-data-file";

export interface ImportPrepareData {
    importId: number | null;
    importDataFile: ImportDataFile;
}
