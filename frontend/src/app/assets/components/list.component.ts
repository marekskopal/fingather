import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';

import { AssetService } from '@app/_services';
import { Asset } from "../../_models/asset";
import {NgbModal} from "@ng-bootstrap/ng-bootstrap";
import {AddEditComponent} from "./add-edit.component";

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit {
    public assets: Asset[]|null = null;

    constructor(
        private assetService: AssetService,
        private modalService: NgbModal,
    ) {}

    ngOnInit() {
        this.assetService.findAll()
            .pipe(first())
            .subscribe(assets => this.assets = assets);
    }

    addAsset() {
        this.modalService.open(AddEditComponent);
    }

    deleteAsset(id: string) {
        const asset = this.assets.find(x => x.id === id);
        //asset.isDeleting = true;
        this.assetService.delete(id)
            .pipe(first())
            .subscribe(() => this.assets = this.assets.filter(x => x.id !== id));
    }
}
