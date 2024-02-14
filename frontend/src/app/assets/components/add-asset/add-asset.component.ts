import { Component, OnInit } from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { Ticker } from '@app/models';
import { AlertService, AssetService, TickerService } from '@app/services';
import { BaseDialog } from '@app/shared/components/dialog/base-dialog';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { Observable, of, OperatorFunction } from 'rxjs';
import {
    catchError, debounceTime, distinctUntilChanged, first, map, switchMap, tap
} from 'rxjs/operators';

@Component({ templateUrl: 'add-asset.component.html' })
export class AddAssetComponent extends BaseDialog implements OnInit {
    public searching: boolean = false;
    public searchFailed: boolean = false;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public model: any;

    public constructor(
        private tickerService: TickerService,
        private assetService: AssetService,
        formBuilder: UntypedFormBuilder,
        activeModal: NgbActiveModal,
        alertService: AlertService,
    ) {
        super(activeModal, formBuilder, alertService);
    }

    public ngOnInit(): void {
        this.form = this.formBuilder.group({
            ticker: ['', Validators.required],
        });
    }

    public search: OperatorFunction<string, readonly Ticker[]> = (text$: Observable<string>) => text$.pipe(
        debounceTime(300),
        distinctUntilChanged(),
        tap(() => (this.searching = true)),
        switchMap((search) => this.tickerService.getTickers(search, 10).pipe(
            map((x) => x.map((ticker) => ticker)),
            tap(() => (this.searchFailed = false)),
            catchError(() => {
                this.searchFailed = true;
                return of([]);
            }),
        ),),
        tap(() => (this.searching = false)),
    );

    public override onSubmit(): void {
        this.submitted = true;

        this.alertService.clear();

        if (this.form.invalid) {
            return;
        }

        this.createAsset();
    }

    private createAsset(): void {
        this.assetService.createAsset(this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Asset added successfully', { keepAfterRouteChange: true });
                    this.activeModal.dismiss();
                    this.assetService.notify();
                },
                error: (error) => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }
}
