import {ChangeDetectionStrategy, Component, inject, OnInit} from '@angular/core';
import { Validators } from '@angular/forms';
import { Ticker } from '@app/models';
import { AssetService, PortfolioService, TickerService
} from '@app/services';
import { BaseDialog } from '@app/shared/components/dialog/base-dialog';
import { Observable, of, OperatorFunction } from 'rxjs';
import {
    catchError, debounceTime, distinctUntilChanged, map, switchMap, tap
} from 'rxjs/operators';
import {BaseForm} from "@app/shared/components/form/base-form";
import {ActivatedRoute, Router} from "@angular/router";

@Component({
    templateUrl: 'add-asset.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AddAssetComponent extends BaseForm implements OnInit {
    private readonly tickerService = inject(TickerService);
    private readonly assetService = inject(AssetService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly route = inject(ActivatedRoute);
    private readonly router = inject(Router);

    public searching: boolean = false;
    public searchFailed: boolean = false;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public model: any;

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

    public formatter = (ticker: Ticker | string): string => {
        if (typeof ticker === 'string') {
            return ticker;
        }

        return `${ticker.ticker} . ${ticker.market.mic}`;
    };

    public async onSubmit(): Promise<void> {
        this.$submitted.set(true);

        this.alertService.clear();

        if (this.form.invalid) {
            return;
        }

        const portfolio = await this.portfolioService.getCurrentPortfolio();
        this.$saving.set(true);
        try {
            this.createAsset(portfolio.id);
        } catch (error: unknown) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.$saving.set(false);
        }
    }

    private async createAsset(portfolioId: number): Promise<void> {
        await this.assetService.createAsset(this.form.value.ticker, portfolioId);

        this.alertService.success('Asset added successfully', { keepAfterRouteChange: true });
        this.assetService.notify();
        this.router.navigate(['../'], { relativeTo: this.route });
    }
}
