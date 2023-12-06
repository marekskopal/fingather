import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';

import { AssetService } from '@app/_services';
import { Asset } from "../../_models";
import {ActivatedRoute} from "@angular/router";

@Component({ templateUrl: 'detail.component.html' })
export class DetailComponent implements OnInit {
    public asset: Asset|null = null;
    public id: string;

    constructor(
      private assetService: AssetService,
      private route: ActivatedRoute,
    ) {}

    ngOnInit() {
        this.id = this.route.snapshot.params['id'];

        this.assetService.getById(this.id)
            .pipe(first())
            .subscribe(asset => this.asset = asset);
    }
}
