import { AbstractEntity } from '@app/models/abstract-entity';
import { DcaPlanTargetTypeEnum } from '@app/models/enums/dca-plan-target-type-enum';

export interface DcaPlan extends AbstractEntity {
    targetType: DcaPlanTargetTypeEnum;
    portfolioId: number;
    assetId: number | null;
    groupId: number | null;
    strategyId: number | null;
    targetName: string;
    amount: string;
    currencyId: number;
    intervalMonths: number;
    startDate: string;
    endDate: string | null;
    annualReturnRate: number;
    monthlyReturnRate: number;
    createdAt: string;
}

export interface DcaPlanProjectionPoint {
    id: number;
    date: string;
    investedCapital: string;
    projectedValue: string;
}

export interface DcaPlanProjection {
    dataPoints: DcaPlanProjectionPoint[];
}
