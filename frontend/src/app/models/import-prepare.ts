import { ImportPrepareTicker } from '@app/models/import-prepare-ticker';

export interface ImportPrepare {
    importId: number;
    notFoundTickers: ImportPrepareTicker[];
    multipleFoundTickers: ImportPrepareTicker[];
    okFoundTickers: ImportPrepareTicker[];
}
