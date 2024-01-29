import { Component, OnInit } from '@angular/core';


import {first} from "rxjs/operators";
import {Portfolio} from "@app/models";
import {PortfolioService} from "@app/services";

@Component({
    selector: 'fingather-portfolio-selector',
    templateUrl: 'portfolio-selector.component.html',
})
export class PortfolioSelectorComponent implements OnInit {
    public portfolios: Portfolio[]|null = null;
    public currentPortfolio: Portfolio;

    public constructor(
        private readonly portfolioService: PortfolioService,
    ) { }

    public async ngOnInit(): Promise<void> {
        this.currentPortfolio = await this.portfolioService.getCurrentPortfolio();

        this.portfolioService.getPortfolios()
            .pipe(first())
            .subscribe((portfolios: Portfolio[]) => {
                this.portfolios = portfolios;
            });
    }

    public changeCurrentPortfolio(event: Event): void {
        const eventTarget = event.target as HTMLSelectElement;
        const currentPortfolioId = parseInt(eventTarget.value);
        this.portfolioService.getPortfolio(currentPortfolioId).subscribe((portfolio: Portfolio) => {
            this.portfolioService.setCurrentPortfolio(portfolio);
            this.currentPortfolio = portfolio;
            this.portfolioService.notify();
        });
    }
}
