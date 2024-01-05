import {Component, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {TransactionService} from "@app/services";
import {TransactionList} from "@app/models/TransactionList";

@Component({
    templateUrl: './list.component.html',
})
export class ListComponent implements OnInit {
    public page: number = 1;
    public pageSize: number = 50;
    public transactionList: TransactionList|null = null;

    public constructor(
        private transactionService: TransactionService,
    ) {
    }

    public ngOnInit(): void {
        this.refreshTransactions();
    }

    public refreshTransactions(): void {
        this.transactionService.getTransactions(null, null, this.pageSize, (this.page - 1) * this.pageSize)
            .pipe(first())
            .subscribe(transactionList => this.transactionList = transactionList);
    }
}
