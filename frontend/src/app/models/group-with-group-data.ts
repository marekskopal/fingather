import { Group, GroupData } from '@app/models';

export interface GroupWithGroupData extends Group {
    percentage: number;
    groupData: GroupData;
}
