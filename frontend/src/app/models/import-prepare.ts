import { ImportPrepareTicker } from '@app/models/import-prepare-ticker';

export class ImportPrepare {
    public importId: number;
    public notFoundTickers: ImportPrepareTicker[];
    public multipleFoundTickers: ImportPrepareTicker[];
    public okFoundTickers: ImportPrepareTicker[];
}
