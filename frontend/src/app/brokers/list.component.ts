import {Component, OnDestroy, OnInit} from '@angular/core';
import {BrokerService, PortfolioService} from '@app/services';
import {ConfirmDialogService} from '@app/services/confirm-dialog.service';
import {NgbModal} from '@ng-bootstrap/ng-bootstrap';
import { first } from 'rxjs/operators';

import { Broker } from '../models/broker';
import {AddEditComponent} from './add-edit.component';

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit, OnDestroy {
    public brokers: Broker[]|null = null;

    public constructor(
        private readonly brokerService: BrokerService,
        private readonly portfolioService: PortfolioService,
        private readonly modalService: NgbModal,
        private readonly confirmDialogService: ConfirmDialogService,
    ) {}

    public ngOnInit(): void {
        this.refreshBrokers();

        this.brokerService.eventEmitter.subscribe(() => {
            this.refreshBrokers();
        });

        this.portfolioService.eventEmitter.subscribe(() => {
            this.refreshBrokers();
        });
    }

    public ngOnDestroy(): void {
        this.brokerService.eventEmitter.unsubscribe();
    }

    public async refreshBrokers(): Promise<void> {
        this.brokers = null;

        const portfolio = await this.portfolioService.getCurrentPortfolio();

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

    public async deleteBroker(id: number): Promise<void> {
        const broker = this.brokers?.find(x => x.id === id);
        if (broker === undefined) {
            return
        }
        broker.isDeleting = true;

        try {
            const confirmed = await this.confirmDialogService.confirm(
                `Delete broker ${broker.name}`,
                `Are you sure to delete broker ${broker.name}?`
            );
            if (!confirmed){
                broker.isDeleting = false;
                return;
            }
        } catch (err) {
            broker.isDeleting = false;
            return;
        }

        this.brokerService.deleteBroker(id)
            .pipe(first())
            .subscribe(() => this.brokers = this.brokers !== null ? this.brokers.filter(x => x.id !== id) : null);
    }
}
