import { AbstractEntity } from '@app/models/abstract-entity';
import { AlertConditionEnum } from '@app/models/enums/alert-condition-enum';
import { AlertRecurrenceEnum } from '@app/models/enums/alert-recurrence-enum';
import { PriceAlertTypeEnum } from '@app/models/enums/price-alert-type-enum';

export interface PriceAlert extends AbstractEntity {
    type: PriceAlertTypeEnum;
    condition: AlertConditionEnum;
    targetValue: string;
    recurrence: AlertRecurrenceEnum;
    cooldownHours: number;
    isActive: boolean;
    lastTriggeredAt: string | null;
    portfolioId: number | null;
    tickerId: number | null;
    tickerTicker: string | null;
    tickerName: string | null;
}
