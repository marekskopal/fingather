import { AbstractEntity } from '@app/models/abstract-entity';

export interface BenchmarkData extends AbstractEntity {
    assetId: number;
    date: string;
    value: number;
}
