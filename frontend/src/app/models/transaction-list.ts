import { Transaction } from '@app/models/transaction';

export interface TransactionList {
    transactions: Transaction[];
    count: number;
}
