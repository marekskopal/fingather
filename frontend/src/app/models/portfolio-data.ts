import {ADataEntity} from '@app/models/ADataEntity';


export class PortfolioData extends ADataEntity {
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
