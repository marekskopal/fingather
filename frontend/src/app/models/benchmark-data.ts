import { AEntity } from '@app/models/AEntity';

export class BenchmarkData extends AEntity {
    public assetId: number;
    public date: string;
    public value: number;
}
