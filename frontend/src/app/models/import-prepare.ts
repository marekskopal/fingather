import { ImportPrepareTicker } from '@app/models/import-prepare-ticker';

export interface ImportPrepare {
    importId: number;
    uuid: string;
    importFileId: number;
    notFoundTickers: ImportPrepareTicker[];
    multipleFoundTickers: ImportPrepareTicker[];
    okFoundTickers: ImportPrepareTicker[];
}
