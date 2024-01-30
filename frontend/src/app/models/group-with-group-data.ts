import {Group, GroupData} from '@app/models';

export class GroupWithGroupData extends Group {
    public percentage: number;
    public groupData: GroupData;
}
