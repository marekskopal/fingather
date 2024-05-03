import { AEntity } from '@app/models/AEntity';

export class Portfolio extends AEntity {
    public currencyId: number;
    public name: string;
    public isDefault: boolean;
}
