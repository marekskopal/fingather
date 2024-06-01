import {
    ChangeDetectionStrategy, Component, OnInit, signal
} from '@angular/core';
import { Portfolio } from '@app/models';
import { PortfolioService } from '@app/services';

@Component({
    selector: 'fingather-portfolio-selector',
    templateUrl: 'portfolio-selector.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PortfolioSelectorComponent implements OnInit {
    private readonly $portfolios = signal<Portfolio[] | null>(null);
    private readonly $currentPortfolio = signal<Portfolio | null>(null);

    public constructor(
        private readonly portfolioService: PortfolioService,
    ) { }

    public async ngOnInit(): Promise<void> {
        const portfolio = await this.portfolioService.getCurrentPortfolio();
        this.$currentPortfolio.set(portfolio);

        const portfolios = await this.portfolioService.getPortfolios();
        this.$portfolios.set(portfolios);
    }

    protected get portfolios(): Portfolio[] | null {
        return this.$portfolios();
    }

    protected get currentPortfolio(): Portfolio | null {
        return this.$currentPortfolio();
    }

    public async changeCurrentPortfolio(event: Event): Promise<void> {
        const eventTarget = event.target as HTMLSelectElement;
        const currentPortfolioId = parseInt(eventTarget.value, 10);
        const portfolio = await this.portfolioService.getPortfolio(currentPortfolioId);
        this.portfolioService.setCurrentPortfolio(portfolio);
        this.$currentPortfolio.set(portfolio);
        this.portfolioService.notify();
    }
}
