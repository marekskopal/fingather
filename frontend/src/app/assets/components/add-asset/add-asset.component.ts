import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import {catchError, debounceTime, distinctUntilChanged, first, map, switchMap, tap} from 'rxjs/operators';
import {AssetWithProperties, Ticker} from "@app/models";
import {AlertService, AssetService, TickerService} from "@app/services";
import {BaseForm} from "@app/shared/components/form/base-form";
import {Observable, of, OperatorFunction} from "rxjs";


@Component({ templateUrl: 'add-asset.component.html' })
export class AddAssetComponent {
    public id: number;
    public isAddMode: boolean;
    public assetTicker: Ticker;
    public searching: boolean = false;
    public searchFailed: boolean = false;
    public model: any;

    public constructor(
        private route: ActivatedRoute,
        private router: Router,
        private tickerService: TickerService,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {

    }

    public search: OperatorFunction<string, readonly string[]> = (text$: Observable<string>) =>
        text$.pipe(
            debounceTime(300),
            distinctUntilChanged(),
            tap(() => (this.searching = true)),
            switchMap((search) =>
                this.tickerService.getTickers(search, 10).pipe(
                    map((x) => x.map((ticker) => ticker.ticker)),
                    tap(() => (this.searchFailed = false)),
                    catchError(() => {
                        this.searchFailed = true;
                        return of([]);
                    }),
                ),
            ),
            tap(() => (this.searching = false)),
        );
}
