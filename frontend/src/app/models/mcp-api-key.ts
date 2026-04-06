import { AbstractEntity } from '@app/models/abstract-entity';

export interface McpApiKey extends AbstractEntity {
    name: string;
    apiKey: string;
    createdAt: string;
}
