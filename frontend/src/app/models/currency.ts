import { AEntity } from '@app/models/AEntity';

export class Currency extends AEntity {
    public code: string;
    public name: string;
    public symbol: string;
}
