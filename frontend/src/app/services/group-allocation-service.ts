import {AbstractGroupWithGroupDataEntity} from "@app/models/abstract-group-with-group-data-entity";

export interface GroupAllocationService {
    getGroupAllocations(portfolioId: number): Promise<AbstractGroupWithGroupDataEntity[]>;
}
