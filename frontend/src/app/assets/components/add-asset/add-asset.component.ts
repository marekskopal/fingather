import {ChangeDetectionStrategy, Component, inject, OnInit} from '@angular/core';
import {ReactiveFormsModule, Validators} from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import {ActivatedRoute, Router, RouterLink} from "@angular/router";
import { Ticker } from '@app/models';
import { AssetService, PortfolioService, TickerService
} from '@app/services';
import { BaseForm } from "@app/shared/components/form/base-form";
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import {NgbHighlight, NgbTypeahead} from "@ng-bootstrap/ng-bootstrap";
import {TranslateModule} from "@ngx-translate/core";
import { Observable, of, OperatorFunction } from 'rxjs';
import {
    catchError, debounceTime, distinctUntilChanged, map, switchMap, tap
} from 'rxjs/operators';

@Component({
    templateUrl: 'add-asset.component.html',
    standalone: true,
    imports: [
        NgbHighlight,
        PortfolioSelectorComponent,
        RouterLink,
        MatIcon,
        TranslateModule,
        ReactiveFormsModule,
        NgbTypeahead,
        InputValidatorComponent,
        SaveButtonComponent
    ],
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
