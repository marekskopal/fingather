import {Component, OnDestroy, OnInit} from '@angular/core';
import { first } from 'rxjs/operators';
import {NgbModal} from "@ng-bootstrap/ng-bootstrap";
import {Portfolio} from "@app/models";
import {PortfolioService} from "@app/services";
import {AddEditComponent} from "@app/portfolios/components/add-edit/add-edit.component";

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit, OnDestroy {
    public portfolios: Portfolio[]|null = null;
    public currentPortfolio: Portfolio;

    public constructor(
        private readonly portfolioService: PortfolioService,
        private readonly modalService: NgbModal,
    ) {}

    public async ngOnInit(): Promise<void> {
        this.currentPortfolio = await this.portfolioService.getCurrentPortfolio();

        this.refreshPortfolios();

        this.portfolioService.eventEmitter.subscribe(() => {
            this.refreshPortfolios();
        });
    }

    public ngOnDestroy(): void {
        this.portfolioService.eventEmitter.unsubscribe();
    }

    public refreshPortfolios(): void {
        this.portfolioService.getPortfolios()
            .pipe(first())
            .subscribe((portfolios: Portfolio[]) => this.portfolios = portfolios);
    }

    public addPortfolio(): void {
        this.modalService.open(AddEditComponent);
    }

    public editPortfolio(id: number): void {
        const addEditComponent = this.modalService.open(AddEditComponent);
        addEditComponent.componentInstance.id = id;
    }

    public deletePortfolio(id: number): void {
        const portfolio = this.portfolios?.find(x => x.id === id);
        if (portfolio === undefined) {
            return
        }
        portfolio.isDeleting = true;
        this.portfolioService.deletePortfolio(id)
            .pipe(first())
            .subscribe(() => this.portfolios = this.portfolios !== null ? this.portfolios.filter(x => x.id !== id) : null);
    }
}
