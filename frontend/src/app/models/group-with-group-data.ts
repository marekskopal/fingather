import {Group, GroupData} from "@app/models";

export class GroupWithGroupData extends Group {
    percentage: number;
    groupData: GroupData;
}
