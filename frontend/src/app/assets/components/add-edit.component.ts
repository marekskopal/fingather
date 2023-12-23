import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { first } from 'rxjs/operators';
import {Asset, Ticker} from "@app/models";
import {AlertService, AssetService, AssetTickerService} from "@app/services";


@Component({ templateUrl: 'add-edit.component.html' })
export class AddEditComponent implements OnInit {
    public form: UntypedFormGroup;
    public id: number;
    public isAddMode: boolean;
    public loading = false;
    public submitted = false;
    public assetTicker: Ticker;

    constructor(
        private formBuilder: UntypedFormBuilder,
        private route: ActivatedRoute,
        private router: Router,
        private assetService: AssetService,
        private alertService: AlertService,
        private assetTickerService: AssetTickerService,
    ) {}

    ngOnInit() {
        this.id = this.route.snapshot.params['id'];
        this.isAddMode = !this.id;

        this.form = this.formBuilder.group({
            ticker: ['', Validators.required],
        });

        if (!this.isAddMode) {
            this.assetService.getById(this.id)
                .pipe(first())
                .subscribe(x => this.form.patchValue(x));
        }
    }

    // convenience getter for easy access to form fields
    get f() { return this.form.controls; }

    onSubmit() {
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

    private createAsset() {
        const formValues = this.form.value;

        this.assetTickerService.getByTicker(formValues.ticker)
            .subscribe(
              (data: Ticker) => {
                  this.assetTicker = { ...data }

                  const asset = new Asset();
                  asset.tickerId = this.assetTicker.id;

                  this.assetService.create(asset)
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

    private updateAsset() {
        this.assetService.update(this.id, this.form.value)
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
