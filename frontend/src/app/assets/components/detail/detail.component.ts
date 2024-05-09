import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { AssetWithProperties } from '@app/models';
import { AssetService } from '@app/services';

@Component({ templateUrl: 'detail.component.html' })
export class DetailComponent implements OnInit {
    public asset: AssetWithProperties | null = null;
    public id: number;

    public constructor(
        private assetService: AssetService,
        private route: ActivatedRoute,
    ) {}

    public async ngOnInit(): Promise<void> {
        this.id = this.route.snapshot.params['id'];

        this.asset = await this.assetService.getAsset(this.id);
    }
}
