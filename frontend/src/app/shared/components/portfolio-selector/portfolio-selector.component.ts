import { ChangeDetectionStrategy, Component, OnInit } from '@angular/core';
import { Portfolio } from '@app/models';
import { PortfolioService } from '@app/services';

@Component({
    selector: 'fingather-portfolio-selector',
    templateUrl: 'portfolio-selector.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PortfolioSelectorComponent implements OnInit {
    public portfolios: Portfolio[] | null = null;
    public currentPortfolio: Portfolio;

    public constructor(
        private readonly portfolioService: PortfolioService,
    ) { }

    public async ngOnInit(): Promise<void> {
        this.currentPortfolio = await this.portfolioService.getCurrentPortfolio();

        this.portfolios = await this.portfolioService.getPortfolios();
    }

    public async changeCurrentPortfolio(event: Event): Promise<void> {
        const eventTarget = event.target as HTMLSelectElement;
        const currentPortfolioId = parseInt(eventTarget.value, 10);
        const portfolio = await this.portfolioService.getPortfolio(currentPortfolioId);
        this.portfolioService.setCurrentPortfolio(portfolio);
        this.currentPortfolio = portfolio;
        this.portfolioService.notify();
    }
}
