import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';

import {NgbModal} from "@ng-bootstrap/ng-bootstrap";
import {AddEditComponent} from "./add-edit.component";
import {Asset} from "@app/models";
import {AssetService} from "@app/services";

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit {
    public assets: Asset[] = [];

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

    deleteAsset(id: number) {
        this.assetService.delete(id)
            .pipe(first())
            .subscribe(() => this.assets = this.assets.filter(x => x.id !== id));
    }
}
