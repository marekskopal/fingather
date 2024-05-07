import { AbstractDataEntity } from '@app/models/abstract-data-entity';

export interface PortfolioData extends AbstractDataEntity {
}

export enum PortfolioDataRangeEnum {
    SevenDays = 'SevenDays',
    OneMonth = 'OneMonth',
    ThreeMonths = 'ThreeMonths',
    SixMonths = 'SixMonths',
    YTD = 'YTD',
    OneYear = 'OneYear',
    All = 'All',
}
