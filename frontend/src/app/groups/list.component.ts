import {Component, OnDestroy, OnInit} from '@angular/core';
import { first } from 'rxjs/operators';

import {AddEditComponent} from "./add-edit.component";
import {NgbModal} from "@ng-bootstrap/ng-bootstrap";
import {Group} from "@app/models";
import {GroupService} from "@app/services";

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit, OnDestroy {
    public groups: Group[]|null = null;

    constructor(
        private groupService: GroupService,
        private modalService: NgbModal,
    ) {}

    ngOnInit() {
        this.groupService.findAll()
            .pipe(first())
            .subscribe(groups => this.groups = groups);

        this.groupService.eventEmitter.subscribe(notified => {
            this.ngOnInit();
        });
    }

    ngOnDestroy() {
        this.groupService.eventEmitter.unsubscribe();
    }

    addGroup() {
        this.modalService.open(AddEditComponent);
    }

    editGroup(id: number) {
        const addEditComponent = this.modalService.open(AddEditComponent);
        addEditComponent.componentInstance.id = id;
    }

    deleteGroup(id: number) {
        const group = this.groups.find(x => x.id === id);
        group.isDeleting = true;
        this.groupService.delete(id)
            .pipe(first())
            .subscribe(() => this.groups = this.groups.filter(x => x.id !== id));
    }
}
