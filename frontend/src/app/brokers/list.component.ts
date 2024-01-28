﻿import {Component, OnDestroy, OnInit} from '@angular/core';
import { first } from 'rxjs/operators';

import {BrokerService, PortfolioService} from '@app/services';
import { Broker } from "../models/broker";
import {AddEditComponent} from "./add-edit.component";
import {NgbModal} from "@ng-bootstrap/ng-bootstrap";

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit, OnDestroy {
    public brokers: Broker[]|null = null;

    public constructor(
        private readonly brokerService: BrokerService,
        private readonly portfolioService: PortfolioService,
        private readonly modalService: NgbModal,
    ) {}

    public ngOnInit(): void {
        this.refreshBrokers();

        this.brokerService.eventEmitter.subscribe(() => {
            this.refreshBrokers();
        });
    }

    public ngOnDestroy(): void {
        this.brokerService.eventEmitter.unsubscribe();
    }

    public async refreshBrokers(): Promise<void> {
        const portfolio = await this.portfolioService.getDefaultPortfolio();

        this.brokerService.getBrokers(portfolio.id)
            .pipe(first())
            .subscribe(brokers => this.brokers = brokers);
    }

    public addBroker(): void {
        this.modalService.open(AddEditComponent, {ariaLabelledBy: 'modal-basic-title'});
    }

    public editBroker(id: number): void {
        const addEditComponent = this.modalService.open(AddEditComponent, {ariaLabelledBy: 'modal-basic-title'});
        addEditComponent.componentInstance.id = id;
    }

    public deleteBroker(id: number): void {
        this.brokerService.deleteBroker(id)
            .pipe(first())
            .subscribe(() => this.brokers = this.brokers !== null ? this.brokers.filter(x => x.id !== id) : null);
    }
}
