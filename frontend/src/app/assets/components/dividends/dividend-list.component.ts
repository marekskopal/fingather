﻿import { Component, OnDestroy, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';
import { ActivatedRoute } from "@angular/router";
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import {PortfolioService, TransactionService} from "@app/services";
import {Transaction, TransactionActionType} from "@app/models";
import {DividendDialogComponent} from "@app/shared/components/dividend-dialog/dividend-dialog.component";

@Component({
    templateUrl: 'dividend-list.component.html',
    selector: 'fingather-dividend-list',
})
export class DividendListComponent implements OnInit, OnDestroy {
    public dividends: Transaction[]|null = null;
    public assetId: number;

    public constructor(
        private readonly transactionService: TransactionService,
        private readonly portfolioService: PortfolioService,
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

    public ngOnDestroy(): void {
        this.transactionService.eventEmitter.unsubscribe();
    }

    public async refreshTransactions(): Promise<void> {
        const portfolio = await this.portfolioService.getCurrentPortfolio();

        this.transactionService.getTransactions(portfolio.id, this.assetId, [TransactionActionType.Dividend])
            .pipe(first())
            .subscribe(dividends => this.dividends = dividends.transactions);
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
        const transaction = this.dividends?.find(x => x.id === id);
        if (transaction === undefined) {
            return;
        }
        transaction.isDeleting = true;
        this.transactionService.deleteTransaction(id)
            .pipe(first())
            .subscribe(() => this.refreshTransactions());
    }
}
