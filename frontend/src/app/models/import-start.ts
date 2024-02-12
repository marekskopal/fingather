import { ImportMapping } from '@app/models/import-mapping';

export class ImportStart {
    public importId: number;
    public importMappings: ImportMapping[];
}
