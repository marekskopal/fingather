import {Component, OnDestroy, OnInit} from '@angular/core';
import { first } from 'rxjs/operators';

import { BrokerService } from '@app/services';
import { Broker } from "../models/broker";
import {AddEditComponent} from "./add-edit.component";
import {NgbModal} from "@ng-bootstrap/ng-bootstrap";

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit, OnDestroy {
    public brokers: Broker[]|null = null;

    constructor(
        private brokerService: BrokerService,
        private modalService: NgbModal,
    ) {}

    ngOnInit() {
        this.brokerService.findAll()
            .pipe(first())
            .subscribe(brokers => this.brokers = brokers);

        this.brokerService.eventEmitter.subscribe(() => {
            this.ngOnInit();
        });
    }

    ngOnDestroy() {
        this.brokerService.eventEmitter.unsubscribe();
    }

    addBroker() {
        this.modalService.open(AddEditComponent, {ariaLabelledBy: 'modal-basic-title'});
    }

    editBroker(id: number) {
        const addEditComponent = this.modalService.open(AddEditComponent, {ariaLabelledBy: 'modal-basic-title'});
        addEditComponent.componentInstance.id = id;
    }

    deleteBroker(id: number) {
        this.brokerService.delete(id)
            .pipe(first())
            .subscribe(() => this.brokers = this.brokers.filter(x => x.id !== id));
    }
}
