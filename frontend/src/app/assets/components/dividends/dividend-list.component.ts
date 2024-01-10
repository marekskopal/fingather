import { Component, OnDestroy, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';
import { ActivatedRoute } from "@angular/router";
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { DividendDialogComponent } from './dividend-dialog.component';
import {TransactionService} from "@app/services";
import {Transaction, TransactionActionType} from "@app/models";

@Component({
    templateUrl: 'dividend-list.component.html',
    selector: 'fingather-dividend-list',
})
export class DividendListComponent implements OnInit, OnDestroy {
    public dividends: Transaction[]|null = null;
    public assetId: number;

    public constructor(
        private transactionService: TransactionService,
        private route: ActivatedRoute,
        private modalService: NgbModal,
    ) {}

    public ngOnInit(): void {
        this.assetId = this.route.snapshot.params['id'];

        this.transactionService.getTransactions(this.assetId, [TransactionActionType.Dividend])
            .pipe(first())
            .subscribe(dividends => this.dividends = dividends.transactions);

        this.transactionService.eventEmitter.subscribe(() => {
            this.ngOnInit();
        });
    }

    public ngOnDestroy(): void {
        this.transactionService.eventEmitter.unsubscribe();
    }

    public addDividend(): void {
        const dividendDialogComponent = this.modalService.open(DividendDialogComponent);
        dividendDialogComponent.componentInstance.assetId = this.assetId;
    }

    public editDividend(id: number): void {
        const dividendDialogComponent = this.modalService.open(DividendDialogComponent);
        dividendDialogComponent.componentInstance.id = id;
    }

    public deleteDividend(id: number): void {
        const dividend = this.dividends?.find(x => x.id === id);
        if (dividend === undefined) {
            return;
        }
        dividend.isDeleting = true;
        this.transactionService.deleteTransaction(id)
            .pipe(first())
            .subscribe(() => this.dividends = this.dividends !== null ? this.dividends.filter(x => x.id !== id) : null);
    }
}
