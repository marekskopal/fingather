import { ImportMapping } from '@app/models/import-mapping';

export interface ImportStart {
    importId: number;
    importMappings: ImportMapping[];
}
