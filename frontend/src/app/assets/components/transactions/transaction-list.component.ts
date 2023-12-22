﻿import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';

import { TransactionService } from '@app/_services';
import { Transaction } from "../../../_models";
import {ActivatedRoute} from "@angular/router";
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { TransactionDialogComponent } from './transaction-dialog.component';

@Component({
    templateUrl: 'transaction-list.component.html',
    selector: 'transaction-list',
})
export class TransactionListComponent implements OnInit {
    public transactions: Transaction[]|null = null;
    public assetId: number;

    constructor(
        private transactionService: TransactionService,
        private route: ActivatedRoute,
        private modalService: NgbModal,
    ) {}

    ngOnInit() {
        this.assetId = this.route.snapshot.params['id'];

        this.transactionService.findByAssetId(this.assetId)
            .pipe(first())
            .subscribe(transactions => this.transactions = transactions);
    }

    addTransaction() {
        this.modalService.open(TransactionDialogComponent);
    }

    deleteTransaction(id: number) {
        const transaction = this.transactions.find(x => x.id === id);
        transaction.isDeleting = true;
        this.transactionService.delete(id)
            .pipe(first())
            .subscribe(() => this.transactions = this.transactions.filter(x => x.id !== id));
    }
}
