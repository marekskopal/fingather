import { AbstractEntity } from '@app/models/abstract-entity';
import { ApiKeyTypeEnum } from "@app/models/enums/api-key-type-enum";

export interface ApiKey extends AbstractEntity {
    type: ApiKeyTypeEnum;
    apiKey: string;
    userKey: string | null;
}
