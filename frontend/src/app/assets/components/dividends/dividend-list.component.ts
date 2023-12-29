import { Component, OnDestroy, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';
import { ActivatedRoute } from "@angular/router";
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { DividendDialogComponent } from './dividend-dialog.component';
import {Dividend} from "@app/models";
import {DividendService} from "@app/services";

@Component({
    templateUrl: 'dividend-list.component.html',
    selector: 'fingather-dividend-list',
})
export class DividendListComponent implements OnInit, OnDestroy {
    public dividends: Dividend[]|null = null;
    public assetId: number;

    constructor(
        private dividendService: DividendService,
        private route: ActivatedRoute,
        private modalService: NgbModal,
    ) {}

    public ngOnInit(): void {
        this.assetId = this.route.snapshot.params['id'];

        this.dividendService.findByAssetId(this.assetId)
            .pipe(first())
            .subscribe(dividends => this.dividends = dividends);

        this.dividendService.eventEmitter.subscribe(() => {
            this.ngOnInit();
        });
    }

    public ngOnDestroy(): void {
        this.dividendService.eventEmitter.unsubscribe();
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
        this.dividendService.delete(id)
            .pipe(first())
            .subscribe(() => this.dividends = this.dividends !== null ? this.dividends.filter(x => x.id !== id) : null);
    }
}
