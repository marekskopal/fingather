import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { first } from 'rxjs/operators';
import {Asset, Ticker} from "@app/models";
import {AlertService, AssetService, TickerService} from "@app/services";
import {BaseForm} from "@app/shared/components/form/base-form";


@Component({ templateUrl: 'add-edit.component.html' })
export class AddEditComponent extends BaseForm implements OnInit {
    public id: number;
    public isAddMode: boolean;
    public assetTicker: Ticker;

    public constructor(
        private route: ActivatedRoute,
        private router: Router,
        private assetService: AssetService,
        private assetTickerService: TickerService,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {
        super(formBuilder, alertService)
    }

    public ngOnInit(): void {
        this.id = this.route.snapshot.params['id'];
        this.isAddMode = !this.id;

        this.form = this.formBuilder.group({
            ticker: ['', Validators.required],
        });

        if (!this.isAddMode) {
            this.assetService.getAsset(this.id)
                .pipe(first())
                .subscribe(x => this.form.patchValue(x));
        }
    }

    public onSubmit(): void {
        this.submitted = true;

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.loading = true;
        if (this.isAddMode) {
            this.createAsset();
        } else {
            this.updateAsset();
        }
    }

    private createAsset(): void {
        const formValues = this.form.value;

        this.assetTickerService.getTicker(formValues.ticker)
            .subscribe(
              (data: Ticker) => {
                  this.assetTicker = { ...data }

                  const asset = new Asset();
                  asset.tickerId = this.assetTicker.id;

                  this.assetService.createAsset(asset)
                      .pipe(first())
                      .subscribe({
                          next: () => {
                              this.alertService.success('Asset added successfully', { keepAfterRouteChange: true });
                              this.router.navigate(['../'], { relativeTo: this.route });
                          },
                          error: error => {
                              this.alertService.error(error);
                              this.loading = false;
                          }
                      });
                  }
            );

    }

    private updateAsset(): void {
        this.assetService.updateAsset(this.id, this.form.value)
            .pipe(first())
            .subscribe({
                next: () => {
                    this.alertService.success('Update successful', { keepAfterRouteChange: true });
                    this.router.navigate(['../../'], { relativeTo: this.route });
                },
                error: error => {
                    this.alertService.error(error);
                    this.loading = false;
                }
            });
    }
}
