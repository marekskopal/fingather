import { ImportMapping } from '@app/models/import-mapping';

export interface ImportStart {
    uuid: string;
    importMappings: ImportMapping[];
}
