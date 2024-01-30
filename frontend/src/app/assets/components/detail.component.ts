import { Component, OnInit } from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {AssetWithProperties} from '@app/models';
import {AssetService} from '@app/services';
import { first } from 'rxjs/operators';

@Component({ templateUrl: 'detail.component.html' })
export class DetailComponent implements OnInit {
    public asset: AssetWithProperties|null = null;
    public id: number;

    public constructor(
        private assetService: AssetService,
        private route: ActivatedRoute,
    ) {}

    public ngOnInit(): void {
        this.id = this.route.snapshot.params['id'];

        this.assetService.getAsset(this.id)
            .pipe(first())
            .subscribe(asset => this.asset = asset);
    }
}
