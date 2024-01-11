import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';

import {ActivatedRoute} from "@angular/router";
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import {Transaction, TransactionActionType} from "@app/models";
import {TransactionService} from "@app/services";
import {TransactionDialogComponent} from "@app/shared/components/transaction-dialog/transaction-dialog.component";

@Component({
    templateUrl: 'transaction-list.component.html',
    selector: 'fingather-transaction-list',
})
export class TransactionListComponent implements OnInit {
    public transactions: Transaction[]|null = null;
    public assetId: number;

    public constructor(
        private transactionService: TransactionService,
        private route: ActivatedRoute,
        private modalService: NgbModal,
    ) {}

    public ngOnInit(): void {
        this.assetId = this.route.snapshot.params['id'];

        this.refreshTransactions();

        this.transactionService.eventEmitter.subscribe(() => {
            this.refreshTransactions();
        });
    }

    public refreshTransactions(): void {
        this.transactionService.getTransactions(this.assetId, [TransactionActionType.Buy, TransactionActionType.Sell])
            .pipe(first())
            .subscribe(transactions => this.transactions = transactions.transactions);
    }

    public addTransaction(assetId: number): void {
        const dialog = this.modalService.open(TransactionDialogComponent);
        dialog.componentInstance.assetId = assetId;
    }

    public deleteTransaction(id: number): void {
        const transaction = this.transactions?.find(x => x.id === id);
        if (transaction === undefined) {
            return;
        }
        transaction.isDeleting = true;
        this.transactionService.deleteTransaction(id)
            .pipe(first())
            .subscribe(() => this.transactions = this.transactions !== null ? this.transactions.filter(x => x.id !== id) : null);
    }
}
