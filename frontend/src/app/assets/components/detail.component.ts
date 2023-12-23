import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';
import {ActivatedRoute} from "@angular/router";
import {Asset} from "@app/models";
import {AssetService} from "@app/services";

@Component({ templateUrl: 'detail.component.html' })
export class DetailComponent implements OnInit {
    public asset: Asset|null = null;
    public id: number;

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
