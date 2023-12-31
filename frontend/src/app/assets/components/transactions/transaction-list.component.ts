﻿import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';

import {ActivatedRoute} from "@angular/router";
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { TransactionDialogComponent } from './transaction-dialog.component';
import {Transaction} from "@app/models";
import {TransactionService} from "@app/services";

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

        this.transactionService.findByAssetId(this.assetId)
            .pipe(first())
            .subscribe(transactions => this.transactions = transactions);
    }

    public addTransaction(): void {
        this.modalService.open(TransactionDialogComponent);
    }

    public deleteTransaction(id: number): void {
        const transaction = this.transactions?.find(x => x.id === id);
        if (transaction === undefined) {
            return;
        }
        transaction.isDeleting = true;
        this.transactionService.delete(id)
            .pipe(first())
            .subscribe(() => this.transactions = this.transactions !== null ? this.transactions.filter(x => x.id !== id) : null);
    }
}
