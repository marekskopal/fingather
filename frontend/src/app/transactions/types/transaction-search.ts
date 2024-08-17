import {TransactionActionType} from "@app/models";

export interface TransactionSearch {
    search: string | null;
    selectedType: TransactionActionType | null;
    created: string | null;
}
